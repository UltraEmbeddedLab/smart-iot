---
title: Provisioning a Raspberry Pi Pico 2 W (Python)
description: Step-by-step guide for registering a Pico 2 W with Smart IoT and running the first provisioning exchange from MicroPython.
---

# Provisioning a Raspberry Pi Pico 2 W (Python)

This guide walks through the full first-boot flow for a Raspberry Pi Pico 2 W running MicroPython:
create the device in Smart IoT, hand its credentials to the board, and let it exchange them for MQTT
connection details on its first boot.

## Prerequisites

- A **Raspberry Pi Pico 2 W** with [MicroPython](https://micropython.org/download/RPI_PICO2_W/) flashed.
- A **Smart IoT account** with access to the UI served by Herd at `https://smart-iot.test` (or your
  production URL). Replace the base URL in the examples with yours.
- A **WiFi network** the Pico can reach.
- The `urequests` and `umqtt.simple` libraries available on the board (shipped in most MicroPython
  firmwares for the Pico 2 W, otherwise install via `mip`).

## Step 1 — Create the device in the UI

1. Sign in and open **Devices** from the sidebar.
2. Click **Add Device**. The wizard has three steps:
   - **Type** — pick `Raspberry Pi Pico`. The type only affects the code snippets and default
     library hints in the firmware generator; provisioning itself is identical across boards.
   - **Name** — give it a recognisable label (e.g. *Greenhouse Sensor*). This is only for you — the
     board never sees it.
   - **Credentials** — the server generates two values and shows them **once**:
     - `Device ID` — a UUID, e.g. `9f2a6b3c-f4e5-4f3d-8f21-7cf5ab101122`.
     - `Secret Key` — a 32-character random string, e.g. `t9qF2vA7nLm8Wx4Jp1Zr6ByK3cHdSeVu`.

> The Secret Key is hashed on save and cannot be recovered. Copy it immediately. If you lose it,
> regenerate a new one from the device page — old firmware using the previous key will stop
> authenticating.

At this point the device row exists in the database with status `provisioning`. Nothing has happened
on the network yet.

## Step 2 — Put the credentials on the board

Create a `config.json` file on the Pico's flash. Everything else the firmware needs will be filled in
later, on first boot.

```json
{
    "wifi_ssid": "YourWiFi",
    "wifi_password": "YourWiFiPassword",
    "api_base": "https://smart-iot.test/api/v1",
    "device_id": "9f2a6b3c-f4e5-4f3d-8f21-7cf5ab101122",
    "secret_key": "t9qF2vA7nLm8Wx4Jp1Zr6ByK3cHdSeVu",
    "mqtt": null
}
```

The `mqtt` field is intentionally `null` — it will be populated by the provisioning response and
saved back to the same file so the next boot can skip the HTTP handshake.

## Step 3 — First boot: the provisioning exchange

On first boot the Pico runs this sequence:

1. **Connect to WiFi** using `wifi_ssid` / `wifi_password` from `config.json`.
2. **POST to `/api/v1/provision`** with a JSON body:
   ```json
   { "device_id": "…", "secret_key": "…" }
   ```
3. Smart IoT validates the pair, marks the device as `online`, and replies with a JSON payload:
   ```json
   {
       "status": "provisioned",
       "device_id": "9f2a6b3c-…",
       "thing_id": "f31e…",
       "mqtt": {
           "host": "mqtt.smart-iot.test",
           "port": 8883,
           "use_tls": true,
           "client_id": "smartiot_9f2a6b3c-…",
           "username": "9f2a6b3c-…",
           "password": "<one-time MQTT token>"
       },
       "topics": {
           "data_out":  "smartiot/<thing_id>/data/out",
           "data_in":   "smartiot/<thing_id>/data/in",
           "cmd_up":    "smartiot/<device_id>/cmd/up",
           "cmd_down":  "smartiot/<device_id>/cmd/down",
           "status":    "smartiot/<device_id>/status"
       },
       "variables": [
           { "variable_name": "temperature", "type": "float", "permission": "read_only", "update_policy": "on_change" }
       ]
   }
   ```
4. The board **persists the `mqtt` block and `topics`** back into `config.json`. From now on the
   HTTP call is no longer needed unless the Secret Key is rotated.

### What the device stores

| Value | Source | Used for |
| --- | --- | --- |
| `device_id` | Entered once during manual setup | HTTP + MQTT identity |
| `secret_key` | Entered once during manual setup | Authenticating `/api/v1/*` calls |
| `mqtt.host` / `mqtt.port` / `mqtt.use_tls` | Provisioning response | Opening the broker connection |
| `mqtt.username` / `mqtt.password` | Provisioning response | MQTT CONNECT packet |
| `mqtt.client_id` | Provisioning response | MQTT CONNECT packet |
| `topics.*` | Provisioning response | `PUBLISH` / `SUBSCRIBE` targets |
| `variables` | Provisioning response | Knowing which readings to publish and at what cadence |

## Step 4 — MQTT connection and normal operation

With the MQTT block cached, every subsequent boot skips straight to the broker:

1. Connect to WiFi.
2. Open a TCP/TLS socket to `mqtt.host:mqtt.port`.
3. Send `CONNECT` with `client_id`, `username`, and `password` from the cached config.
4. **Subscribe** to:
   - `topics.cmd_down` — server-originated commands (reboots, remote actions).
   - `topics.data_in` — value updates the server pushes to you (e.g. `read_write` variables).
5. **Publish**:
   - `topics.status` — `"online"` on connect, `"offline"` as the MQTT Last Will payload.
   - `topics.data_out` — JSON payloads of cloud variable updates, respecting each variable's
     `update_policy` and `update_parameter` (e.g. every 30 seconds for `periodically`).

In parallel the firmware should periodically call `POST /api/v1/heartbeat` with the
`X-Device-ID` and `X-Secret-Key` headers so the web UI can show a fresh "last seen" timestamp even
when the board has nothing useful to publish.

## Minimal MicroPython example

```python
import json
import network
import time
import urequests
from umqtt.simple import MQTTClient

CONFIG_PATH = "config.json"

def load_config():
    with open(CONFIG_PATH) as f:
        return json.load(f)

def save_config(cfg):
    with open(CONFIG_PATH, "w") as f:
        json.dump(cfg, f)

def connect_wifi(ssid, password):
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    if not wlan.isconnected():
        wlan.connect(ssid, password)
        while not wlan.isconnected():
            time.sleep(0.5)
    return wlan

def provision(cfg):
    body = {"device_id": cfg["device_id"], "secret_key": cfg["secret_key"]}
    r = urequests.post(cfg["api_base"] + "/provision", json=body)
    if r.status_code != 200:
        raise RuntimeError("provisioning failed: %d %s" % (r.status_code, r.text))
    data = r.json()
    r.close()
    cfg["mqtt"] = data["mqtt"]
    cfg["topics"] = data["topics"]
    cfg["variables"] = data["variables"]
    save_config(cfg)
    return cfg

def connect_mqtt(cfg):
    mqtt = cfg["mqtt"]
    client = MQTTClient(
        client_id=mqtt["client_id"],
        server=mqtt["host"],
        port=mqtt["port"],
        user=mqtt["username"],
        password=mqtt["password"],
        ssl=mqtt["use_tls"],
        keepalive=60,
    )
    client.set_last_will(cfg["topics"]["status"], b"offline", retain=True)
    client.connect()
    client.publish(cfg["topics"]["status"], b"online", retain=True)
    return client

def main():
    cfg = load_config()
    connect_wifi(cfg["wifi_ssid"], cfg["wifi_password"])

    if cfg.get("mqtt") is None:
        cfg = provision(cfg)

    client = connect_mqtt(cfg)

    while True:
        payload = json.dumps({"temperature": 21.3})
        client.publish(cfg["topics"]["data_out"], payload)
        time.sleep(30)

main()
```

## What happens on subsequent boots

- `config.json` already has an `mqtt` block, so `provision()` is skipped entirely.
- The Pico goes straight to `connect_mqtt()` and resumes publishing.
- If the Secret Key is rotated in the UI, MQTT CONNECT starts failing with auth errors. Clear the
  `mqtt` field in `config.json`, update `secret_key` with the new value, and the next boot
  re-provisions automatically.

## Troubleshooting

- **`401 Invalid device credentials`** — the `device_id` / `secret_key` pair does not match. Double
  check both values. Remember the Secret Key is shown only once.
- **Heartbeat returns `401`** — the headers must be `X-Device-ID` and `X-Secret-Key`; a missing or
  malformed header triggers the device auth middleware to reject the request.
- **Provisioning endpoint returns `429`** — the `/provision` route is rate-limited to 5 requests per
  minute per IP. Wait a minute before retrying, or cache the response so you are not re-provisioning
  on every boot.

---
title: Introduction
description: Smart IoT is a Laravel-powered platform for managing IoT devices, cloud variables, dashboards, and automation triggers.
---

# Introduction

**Smart IoT** is a platform for provisioning IoT devices, ingesting telemetry, building live dashboards, and
reacting to events through configurable triggers. It is built on Laravel, Livewire, and Flux UI so you can
extend it using the same stack you already know.

## What you can do

Smart IoT covers the full lifecycle of an IoT deployment: register a device, stream measurements as cloud
variables, visualize them on a dashboard, and fire triggers when values cross your thresholds.

- **Provision devices** with a one-time secret, an auto-generated device ID, and per-device API rate limiting.
- **Model things and variables** so each physical device exposes a clear contract of readable and writable points.
- **Build dashboards** by dropping widgets onto a grid and binding them to cloud variables.
- **Automate with triggers** that compare variable values against operators and fire email or webhook actions.
- **Generate firmware** pre-configured with your WiFi credentials and device identity.

## How to get started

1. Open the **Devices** section from the sidebar and create your first device.
2. Configure the **Things** that this device will expose — each thing groups related variables.
3. Create a **Dashboard** and add widgets bound to the variables you care about.
4. Set up a **Trigger** to get notified when a value leaves its expected range.

## How these docs are organized

The documentation is task-oriented. Pick the section that matches what you want to do and follow it end to
end. New pages live under `resources/docs` as markdown files and register themselves through
`config/docs.php`.

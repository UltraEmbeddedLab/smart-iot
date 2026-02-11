<?php declare(strict_types=1);

namespace App\Mail;

use App\Models\Thing;
use App\Models\Trigger;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TriggerAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Trigger $trigger,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Trigger Alert: '.$this->trigger->name,
        );
    }

    public function content(): Content
    {
        $this->trigger->loadMissing('cloudVariable.thing');

        $cloudVariable = $this->trigger->cloudVariable;

        /** @var Thing $thing */
        $thing = $cloudVariable->thing;

        return new Content(
            view: 'mail.trigger-alert',
            with: [
                'triggerName' => $this->trigger->name,
                'variableName' => $cloudVariable->name,
                'thingName' => $thing->name,
                'currentValue' => $cloudVariable->last_value['value'] ?? 'N/A',
                'condition' => $this->trigger->operator->symbol().' '.$this->trigger->value,
                'triggeredAt' => $this->trigger->last_triggered_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
            ],
        );
    }
}

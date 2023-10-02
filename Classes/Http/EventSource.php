<?php

namespace Sharp\Classes\Http;

use Sharp\Classes\Core\Configurable;

class EventSource
{
    use Configurable;

    const MESSAGE_END = "\n\n";
    const LINE_END = "\n";

    protected $started = false;

    public static function getDefaultConfiguration(): array
    {
        return [
            'use_default_event_name' => true,
            'start_event' => 'event-source-start',
            'end_event' => 'event-source-end',
            'die_on_end' => true
        ];
    }

    public function start(string $startEvent=null)
    {
        if ($this->started)
            return;
        $this->started = true;

        header('Cache-Control: no-store');
        header('Content-Type: text/event-stream');

        if ($this->configuration['use_default_event_name'])
            $startEvent ??= $this->configuration['start_event'];

        if ($startEvent)
            $this->send($startEvent);
    }

    protected function sendMessage(string $message)
    {
        if (!$this->started)
            $this->start();

        echo $message . self::MESSAGE_END;
        flush();

        if (connection_aborted())
            die;
    }

    public function send(string $event, mixed $data=null, $id=null, int $retry=null)
    {
        $message = join(self::LINE_END, [
            "event: $event" ,
            'data: '. json_encode($data),
            ($id ? "id: $id": ''),
            ($retry ? "retry: $retry": '')
        ]);

        $this->sendMessage($message);
    }

    public function data(mixed $data)
    {
        $this->sendMessage('data: '. json_encode($data));
    }

    public function end(string $endEvent=null)
    {
        if ($this->configuration['use_default_event_name'])
            $endEvent ??= $this->configuration['end_event'];

        if ($endEvent)
            $this->send($endEvent);

        $this->started = false;

        if ($this->configuration['die_on_end'])
            die;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }
}
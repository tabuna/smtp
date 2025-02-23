<?php

namespace Esplora\Lumos\Actions;

use Esplora\Lumos\Connections\Session;
use Esplora\Lumos\Status;

/**
 * Обрабатывает команду HELO.
 * Используется клиентом для представления сервера.
 * TODO: есть еще EHLO для расширенной информации.
 * Сервер должен вернуть `250 OK` в случае успеха.
 */
class Helo
{
    public function handle(Session $session, string $argument): string
    {
        if (empty($argument)) {
            return Status::PARAMETERS_ERROR->response('HELO <hostname>');
        }

        $session->setClientHostname($argument);

        return Status::SUCCESS->response("Hello $argument");
    }
}

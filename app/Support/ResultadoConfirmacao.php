<?php

namespace App\Support;

/** O que aconteceu ao abrir o link de confirmação de e-mail. */
enum ResultadoConfirmacao
{
    case Confirmado;
    case JaConfirmado;
    case Invalido;
}

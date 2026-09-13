<?php

namespace App\Payments;

/** Dados que o cliente digita. Nada disso é gravado: só a bandeira e os 4 últimos. */
readonly class DadosCartao
{
    public function __construct(
        public string $numero,
        public string $nome,
        public string $validade,
        public string $cvv,
        public int $parcelas,
    ) {}

    public function apenasDigitos(): string
    {
        return preg_replace('/\D/', '', $this->numero);
    }

    public function ultimosQuatro(): string
    {
        return substr($this->apenasDigitos(), -4);
    }

    public function bandeira(): string
    {
        $numero = $this->apenasDigitos();

        return match (true) {
            str_starts_with($numero, '4') => 'visa',
            (bool) preg_match('/^5[1-5]|^2[2-7]/', $numero) => 'mastercard',
            (bool) preg_match('/^3[47]/', $numero) => 'amex',
            (bool) preg_match('/^(36|38|30[0-5])/', $numero) => 'diners',
            (bool) preg_match('/^(4011|4312|4389|5041|6062)/', $numero) => 'elo',
            default => 'outra',
        };
    }
}

<?php

namespace PixlMint\CMS\Models;

use Nacho\ORM\AbstractModel;
use Nacho\ORM\ModelInterface;
use Nacho\ORM\TemporaryModel;

class Secret extends AbstractModel implements ModelInterface
{
    private ?string $secret = null;

    public static function init(TemporaryModel $data, int $id): ModelInterface
    {
        return new Secret($id, $data->get('secret'));
    }

    public function __construct(int $id, ?string $secret)
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret(?string $secret)
    {
        $this->secret = $secret;
    }

    public function toArray(): array
    {
        return [
            'secret' => $this->secret,
        ];
    }
}
<?php


class Player
{
    public const USER = 'user';
    public const COMPUTER = 'computer';

    protected string $type;

    public static function createUser(): self
    {
        $player = new self;

        $player->type = self::USER;

        return $player;
    }

    public static function createComputer(): self
    {
        $player = new self;

        $player->type = self::COMPUTER;

        return $player;
    }

    public function getPieceIndicator(): string
    {
        if ($this->isUser()) {
            return 'X';
        }

        return 'O';
    }

    public function getRowMultiplier(): int
    {
        if ($this->isUser()) {
            // User pieces must move up;
            return 1;
        }

        // Computer pieces must move down.
        return -1;
    }


    public function equals(Player $player): bool
    {
        return $this->type === $player->type;
    }

    public function isUser(): bool
    {
        return $this->type === self::USER;
    }

    public function isComputer(): bool
    {
        return $this->type === self::COMPUTER;
    }
}

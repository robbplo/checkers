<?php


class Piece
{
    public int $row;
    public int $column;
    protected Player $owner;

    public function __construct(int $row, int $column, Player $owner)
    {
        $this->row = $row;
        $this->column = $column;
        $this->owner = $owner;
    }

    public function move(int $row, int $column): void
    {
        $this->row = $row;
        $this->column = $column;
    }

    public function pieceIndicator(): string
    {
        if ($this->owner->isUser()) {
            return 'X';
        }

        return 'O';
    }

    public function isAtPosition(int $row, int $column): bool
    {
        return $this->row === $row && $this->column === $column;
    }

}

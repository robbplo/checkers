<?php


class Piece
{
    public function __construct(
        public int $row,
        public int $column,
        public Player $owner
    ) {
    }

    public function move(int $row, int $column): void
    {
        $this->row = $row;
        $this->column = $column;
    }

    public function isAtPosition(int $row, int $column): bool
    {
        return $this->row === $row && $this->column === $column;
    }

}

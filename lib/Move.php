<?php


class Move
{
    public function __construct(
        public Piece $piece,
        public int $toRow,
        public int $toColumn,
        public bool $jumpingMove = false,
    ) {
    }

}

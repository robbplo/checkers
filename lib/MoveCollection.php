<?php

class MoveCollection
{
    /** @var Move[] */
    protected array $moves = [];

    public function __construct(
        protected PieceCollection $pieces,
    ) {
    }

    public function containsJumpingMove(Player $player)
    {
        $this->findJumps();

        $jumpingMoves = array_filter(
            $this->moves,
            fn (Move $move) => $move->jumpingMove &&
                $move->piece->owner->equals($player)
        );

        return ! empty($jumpingMoves);
    }

    protected function findJumps(): void
    {
        foreach ($this->pieces->getPieces() as $piece) {
            $rowModifier = 1 * $piece->owner->getRowMultiplier();
            $row = $piece->row + $rowModifier;
            $leftColumn = $piece->column - 1;
            $rightColumn = $piece->column + 1;

            if (
                $this->pieces->pieceInPosition($row, $leftColumn) &&
                $this->pieces->validateMove($piece, $row + $rowModifier, $leftColumn - 1)
            ) {
                $this->moves[] = new Move(
                    $piece,
                    $row + $rowModifier,
                    $leftColumn - 1,
                    true
                );
            }

            if (
                $this->pieces->pieceInPosition($row, $rightColumn) &&
                $this->pieces->validateMove($piece, $row + $rowModifier, $rightColumn + 1)
            ) {
                $this->moves[] = new Move(
                    $piece,
                    $row + $rowModifier,
                    $rightColumn + 1,
                    true
                );
            }
        }
    }


}

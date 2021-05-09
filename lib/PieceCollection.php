<?php


class PieceCollection
{
    /** @var Piece[] $pieces */
    protected $pieces = [];
    protected MoveCollection $moves;

    public function __construct(protected Game $game)
    {
        $this->setupPieces();
        $this->moves = new MoveCollection($this);
    }

    public function getPieces(): array
    {
        return $this->pieces;
    }

    public function pieceInPosition(int $row, int $column): Piece|null
    {
        $foundPieces = array_filter($this->pieces, fn (Piece $piece) => $piece->isAtPosition($row, $column));

        if (empty($foundPieces)) {
            return null;
        }

        return array_values($foundPieces)[0];
    }

    public function move(int $fromRow, int $fromColumn, int $toRow, int $toColumn): bool
    {
        $piece = $this->pieceInPosition($fromRow, $fromColumn);

        if (! $this->validateMove($piece, $toRow, $toColumn)) {
            return false;
        }

        $enemyPiece = $this->moveJumpsOverEnemyPiece($piece, $toRow, $toColumn);

        if (! $this->enforceJumpingMove($enemyPiece)) {
            return false;
        }

        if ($enemyPiece !== false) {
            $this->removePiece($enemyPiece);
        }

        $piece->move($toRow, $toColumn);

        // Message is being set by validator, so this is a quick fix.
        $this->game->setMessage(null);

        return true;
    }

    public function removePiece(Piece $piece): void
    {
        $remaining = array_filter(
            $this->pieces,
            fn (Piece $x) => ! ($x->row === $piece->row && $x->column === $piece->column)
        );

        $this->pieces = array_values($remaining);
    }

    public function moveJumpsOverEnemyPiece(Piece $piece, int $row, int $column): bool|Piece
    {
        // Assuming piece moved 2 squares diagonally, since it passed validation.
        $rowBetween = $row - $piece->owner->getRowMultiplier();

        if ($column > $piece->column) {
            $columnBetween = $column - 1;
        } else {
            $columnBetween = $column + 1;
        }

        $jumpingOverPiece = $this->pieceInPosition($rowBetween, $columnBetween);

        if ($jumpingOverPiece === null || $jumpingOverPiece->owner->equals($piece->owner)) {
            return false;
        }

        return $jumpingOverPiece;
    }

    public function validateMove(?Piece $piece, int $row, int $column): bool
    {
        if ($piece === null) {
            $this->game->setMessage("No piece in that position.");
            return false;
        }

        if (! $piece->owner->equals($this->game->currentTurn)) {
            $this->game->setMessage('You can only move your own pieces.');
            return false;
        }

        if (
            ($piece->owner->isUser() && $row < $piece->row) ||
            ($piece->owner->isComputer() && $row > $piece->row)
        ) {
            $this->game->setMessage('Backwards movement is not allowed.');
            return false;
        }

        $diagonalMove = (
            $row === $piece->row + 1 * $piece->owner->getRowMultiplier()
            && ($column === $piece->column - 1 || $column === $piece->column + 1)
        );

        $jumpingMove = (
            $row === $piece->row + 2 * $piece->owner->getRowMultiplier()
            && ($column === $piece->column - 2 || $column === $piece->column + 2)
        );

        if (! $diagonalMove && ! $jumpingMove) {
            $this->game->setMessage('You can only move pieces one square diagonally.');
            return false;
        }

        if ($row < 1 || $row > 8 || $column < 1 || $column > 8) {
            $this->game->setMessage('You cannot move pieces outside of board.');
            return false;
        }

        if ($this->pieceInPosition($row, $column)) {
            $this->game->setMessage('You can only move to empty squares.');
            return false;
        }

        if ($jumpingMove && ! $this->moveJumpsOverEnemyPiece($piece, $row, $column)) {
            $this->game->setMessage('You can only jump over enemy pieces.');
            return false;
        }

        return true;
    }

    protected function enforceJumpingMove(bool|Piece $enemyPiece): bool
    {
        if (!$enemyPiece && $this->moves->containsJumpingMove($this->game->currentTurn)) {
            $this->game->setMessage('You must jump over an enemy piece if possible.');
            return false;
        }

        return true;
    }

    protected function setupPieces(): void
    {
        $rows = $columns = range(1, 8);

        foreach ($rows as $row) {
            if ($row >= 1 && $row <= 3) {
                $rowOwner = $this->game->user;
            } elseif ($row >= 6 && $row <= 8) {
                $rowOwner = $this->game->computer;
            } else {
                continue;
            }

            $rowIsEven = $row % 2 === 0;

            foreach ($columns as $column) {
                $columnIsEven = $column % 2 === 0;

                if ($rowIsEven && $columnIsEven) {
                    $this->pieces[] = new Piece($row, $column, $rowOwner);
                } elseif (! $rowIsEven && ! $columnIsEven) {
                    $this->pieces[] = new Piece($row, $column, $rowOwner);
                }
            }
        }
    }
}

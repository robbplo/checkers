<?php


class PieceCollection
{
    protected Game $game;
    /** @var Piece[] $pieces */
    protected $pieces = [];

    public function __construct(Game $game)
    {
        $this->game = $game;

        $this->setupPieces();
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

        if ($enemyPiece !== null) {
            $this->removePiece($enemyPiece);
        }

        $piece->move($toRow, $toColumn);

        return true;
    }

    public function removePiece(Piece $piece): void
    {
        $remaining = array_filter(
            $this->pieces,
            fn (Piece $x) => !($x->row === $piece->row && $x->column === $piece->column)
        );

        $this->pieces = array_values($remaining);
    }

    public function moveJumpsOverEnemyPiece(Piece $piece, int $row, int $column): bool | Piece
    {
        // Assuming piece moved 2 squares diagonally, since it passed validation.
        $rowBetween = $row - $this->getXDirectionMultiplier();

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

    protected function getXDirectionMultiplier(): int
    {
        if ($this->game->currentTurn->isUser()) {
            // User pieces must move up;
            return 1;
        }

        // Computer pieces must move down.
        return -1;
    }

    protected function validateMove(?Piece $piece, int $row, int $column): bool
    {
        if ($piece === null) {
            $this->game->setMessage("No piece in that position.");
            return false;
        }

        if (!$piece->owner->equals($this->game->currentTurn)) {
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
            $row === $piece->row + 1 * $this->getXDirectionMultiplier()
            && ($column === $piece->column - 1 || $column === $piece->column + 1)
        );

        $jumpingMove = (
            $row === $piece->row + 2 * $this->getXDirectionMultiplier()
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

        if ($jumpingMove && !$this->moveJumpsOverEnemyPiece($piece, $row, $column)) {
            $this->game->setMessage('You can only jump over enemy pieces.');
            return false;
        }

        return true;
    }

    protected function setupPieces(): void
    {
        // @todo remove testing piece
        $this->pieces[] = new Piece(4, 2, $this->game->computer);

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

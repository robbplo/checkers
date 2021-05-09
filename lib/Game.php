<?php

class Game
{
    /** @var Piece[] */
    protected array $pieces;

    protected Player $user;
    protected Player $computer;

    protected ?string $message = null;

    protected bool $inProgress = true;

    public static function start(): void
    {
        $game = new self;

        $game->setupPlayers();
        $game->setupPieces();


        while ($game->inProgress) {
            $game->renderBoard();
            $game->displayMessage();
            $game->promptMove();
        }
    }

    public function renderBoard(): void
    {
        // Clear command line
        system('clear');

        $spacer = '  _ _ _ _ _ _ _ _';
        $rows = range(8, 1);
        $columns = range(1, 8);

        foreach ($rows as $row) {
            echo $spacer . "\n";

            echo $row;
            foreach ($columns as $column) {
                echo '|';

                $piece = $this->pieceInPosition($row, $column);
                if ($piece === null) {
                    echo ' ';
                } else {
                    echo $piece->pieceIndicator();
                }
            }
            echo "|\n";
        }

        $columnNumbers = '  ' . implode(' ', $columns);

        echo $spacer . "\n";
        echo $columnNumbers . "\n\n";
    }

    protected function displayMessage(): void
    {
        if ($this->message === null) {
            return;
        }

        echo $this->message . "\n";
        $this->message = null;
    }

    protected function setMessage(string $message): void
    {
        $this->message = $message;
    }

    protected function promptMove(): bool
    {
        $input = readline('Make your move:');

        if (! preg_match('/^\d \d \d \d$/', $input)) {
            echo "Invalid input. \n\n";
            return $this->promptMove();
        }

        [$fromRow, $fromColumn, $toRow, $toColumn] = explode(' ', $input);

        $piece = $this->pieceInPosition($fromRow, $fromColumn);

        return $this->movePiece($piece, $toRow, $toColumn);
    }

    protected function movePiece(?Piece $piece, int $row, int $column): bool
    {
        if (! $this->validateMove($piece, $row, $column)) {
            return false;
        }

        $enemyPiece = $this->moveJumpsOverEnemyPiece($piece, $row, $column);

        if ($enemyPiece !== null) {
            $this->removePiece($enemyPiece);
        }

        $piece->move($row, $column);

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

    protected function moveJumpsOverEnemyPiece(Piece $piece, int $row, int $column): bool | Piece
    {
        // Assuming piece moves 2 squares diagonally, since it passed validation.

        $rowBetween = $row - 1;

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

    protected function validateMove(?Piece $piece, int $row, int $column): bool
    {
        if ($piece === null) {
            $this->setMessage("No piece in position {$row} {$column}.");
            return false;
        }

        // Need to change when implementing AI
        if (!$piece->owner->isUser()) {
            $this->setMessage('You can only move your own pieces.');
            return false;
        }

        if ($row < $piece->row) {
            $this->setMessage('Backwards movement is not allowed.');
            return false;
        }

        $diagonalMove = (
            $row === $piece->row + 1
            && ($column === $piece->column - 1 || $column === $piece->column + 1)
        );

        $jumpingMove = (
            $row === $piece->row + 2
            && ($column === $piece->column - 2 || $column === $piece->column + 2)
        );

        if (! $diagonalMove && ! $jumpingMove) {
            $this->setMessage('You can only move pieces one square diagonally.');
            return false;
        }

        if ($row < 1 || $row > 8 || $column < 1 || $column > 8) {
            $this->setMessage('You cannot move pieces outside of board.');
            return false;
        }

        if ($this->pieceInPosition($row, $column)) {
            $this->setMessage('You can only move to empty squares.');
            return false;
        }

        if ($jumpingMove && !$this->moveJumpsOverEnemyPiece($piece, $row, $column)) {
            $this->setMessage('You can only jump over enemy pieces.');
            return false;
        }

        return true;
    }

    protected function pieceInPosition(int $row, int $column): Piece|null
    {
        $foundPieces = array_filter($this->pieces, fn (Piece $piece) => $piece->isAtPosition($row, $column));

        if (empty($foundPieces)) {
            return null;
        }

        return array_values($foundPieces)[0];
    }

    protected function setupPlayers(): void
    {
        $this->user = Player::createUser();
        $this->computer = Player::createComputer();
    }

    protected function setupPieces(): void
    {
        // @todo remove testing piece
        $this->pieces[] = new Piece(4, 2, $this->computer);

        $rows = $columns = range(1, 8);

        foreach ($rows as $row) {
            if ($row >= 1 && $row <= 3) {
                $rowOwner = $this->user;
            } elseif ($row >= 6 && $row <= 8) {
                $rowOwner = $this->computer;
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

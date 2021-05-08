<?php

class Game
{
    /** @var Piece[] */
    protected array $pieces;

    protected Player $user;
    protected Player $computer;

    protected bool $inProgress = true;

    public static function start(): void
    {
        $game = new self;

        $game->setupPlayers();
        $game->setupPieces();


        while ($game->inProgress) {
            $game->render();
            $game->promptMove();
        }
    }

    public function render(): void
    {
        // Clear command line
        system('clear');

        $spacer = '  _ _ _ _ _ _ _ _';
        $rows = range(8, 1);
        $columns = range(1, 8);

        foreach ($rows as $row) {
            print $spacer . "\n";

            print $row;
            foreach ($columns as $column) {
                print '|';

                $piece = $this->pieceInPosition($row, $column);
                if ($piece === null) {
                    print ' ';
                } else {
                    print $piece->pieceIndicator();
                }
            }
            print "|\n";
        }

        $columnNumbers = '  ' . implode(' ', $columns);

        print $spacer . "\n";
        print $columnNumbers . "\n\n";
    }

    protected function promptMove(): bool
    {
        $input = readline('Make your move:');

        if (!preg_match('/^\d \d \d \d$/', $input)) {
            print "Invalid input. \n\n";
            return $this->promptMove();
        }

        [$fromRow, $fromColumn, $toRow, $toColumn] = explode(' ', $input);

        $piece = $this->pieceInPosition($fromRow, $fromColumn);

        if ($piece === null) {
            print "No piece in position {$fromRow} {$fromColumn}";
            return false;
        }

        return $piece->move($toRow, $toColumn);
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

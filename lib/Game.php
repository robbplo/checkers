<?php

class Game
{
    public PieceCollection $pieces;

    public Player $user;
    public Player $computer;

    public ?string $message = null;

    public bool $inProgress = true;

    public static function start(): void
    {
        $game = new self;

        $game->setupPlayers();
        $game->pieces = new PieceCollection($game);


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

                $piece = $this->pieces->pieceInPosition($row, $column);
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

    public function setMessage(string $message): void
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

        return $this->pieces->move($fromRow, $fromColumn, $toRow, $toColumn);
    }


    protected function setupPlayers(): void
    {
        $this->user = Player::createUser();
        $this->computer = Player::createComputer();
    }
}

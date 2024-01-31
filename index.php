<?php
class GameState {
    public $currentState;
    public $whatToAdd;
    public $movesCounter;
    private static $defaultCurrentState = array(
        array('', '', ''),
        array('', '', ''),
        array('', '', '')
    );
    public $expirationTime;
    public $gameEnd = 0;

    function __construct($cookies, $post) {
        if ($this->shouldStartGameFromBeginning($cookies, $post)) {
            $this->startGameFromBeginning();
        } else {
            $this->readGameFromCookies($cookies);
        }
        $this->expirationTime = time() + 3600;
    }

    function shouldStartGameFromBeginning($cookies, $post) {
        return $post["restart-game"] || $cookies["current-state"] == null;
    }
    private function startGameFromBeginning()
    {
        $this->currentState = GameState::$defaultCurrentState;
        $this->movesCounter = 0;
        $this->whatToAdd = 'x';
    }

    private function readGameFromCookies($cookies)
    {
        $currentStateJson = $cookies["current-state"];
        $this->currentState = json_decode($currentStateJson);
        $this->whatToAdd = $cookies["what-to-add"];
        $this->movesCounter = $cookies["moves-counter"];
    }

    public function nextMove(){
        setcookie("current-state", json_encode($this->currentState), $this->expirationTime);
        setcookie("what-to-add", $this->whatToAdd, $this->expirationTime);
        setcookie("moves-counter", $this->movesCounter, $this->expirationTime);
    }

    public function checkWin($board, $symbol) {
        $win1 = $board[0][0].$board[0][1].$board[0][2]; // 1 -
        $win2 = $board[1][0].$board[1][1].$board[1][2]; // 2 -
        $win3 = $board[2][0].$board[2][1].$board[2][2]; // 3 -
        $win4 = $board[0][0].$board[1][0].$board[2][0]; // 1 |
        $win5 = $board[0][1].$board[1][1].$board[2][1]; // 2 |
        $win6 = $board[0][2].$board[1][2].$board[2][2]; // 3 |
        $win7 = $board[0][0].$board[1][1].$board[2][2]; // \
        $win8 = $board[2][0].$board[1][1].$board[0][2]; // /

        return $win1 === $symbol ||
            $win2 === $symbol ||
            $win3 === $symbol ||
            $win4 === $symbol ||
            $win5 === $symbol ||
            $win6 === $symbol ||
            $win7 === $symbol ||
            $win8 === $symbol;
    }
}

$gameState = new GameState($_COOKIE, $_POST);

if ($_POST["field-coordinates"]) {
    $fieldCoordinates = json_decode($_POST["field-coordinates"]);
    $gameState->currentState[$fieldCoordinates->x][$fieldCoordinates->y] = $gameState->whatToAdd;
    $gameState->movesCounter++;

    if ($gameState->checkWin($gameState->currentState, 'xxx')) {
        echo 'X won';
        $gameState->gameEnd = 1;
    } elseif ($gameState->checkWin($gameState->currentState, 'ooo')) {
        echo 'O won';
        $gameState->gameEnd = 1;
    } elseif ($gameState->movesCounter == 9) {
        echo 'Draw';
    }

    if ($gameState->whatToAdd == 'o') {
        $gameState->whatToAdd = 'x';
    } elseif ($gameState->whatToAdd == 'x') {
        $gameState->whatToAdd = 'o';
    }
}

$gameState->nextMove();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tic-Tac-Toe Game</title>
    <style>
        table, td, tr {
            border: 1px solid;
            border-collapse: collapse;
        }
        td {
            height: 200px;
            width: 200px;
            text-align: center;
        }
        table {
            border: none;
            font-size: 100px;
        }
        table td, table tr:first-child {
            border-top: 0;
            border-left: 0;
        }
        table tr {
            border-left: 0;
            border-right: 0;
        }
        table tr:last-child {
            border-bottom: 0;
        }
        table tr td:last-child {
            border-right: 0;
            border-bottom: 0;
        }
        table tr:last-child td {
            border-bottom: 0;
        }
        form {
            display: flex;
        }
        table button {
            height: 200px;
            width: 200px;
            display: block;
        }
    </style>
</head>
<body>
<div class="board">
    <table style="margin: 50px auto auto;">
        <?php
        foreach ($gameState->currentState as $x => $item) {
            echo "<tr>";
            foreach ($item as $y => $value) {
                if ($value == '' && $gameState->gameEnd == 0) {
                    echo '<td>
            <form method="post" action="/tic-tac-toe/index.php">
            <input type="hidden" name="field-coordinates" value=\'' . json_encode(array("x" => $x, "y" => $y)) . '\'>
            <button type="submit"> </button>
            </form>';
                    echo strtoupper($value);
                    echo "</td>";
                } else {
                    echo '<td>'.strtoupper($value).'</td>';
                }
            }
            echo "</tr>";
        }
        ?>
    </table>
    <form method="post" action="/tic-tac-toe/index.php">
        <input type="hidden" name="restart-game" value="1">
        <button type="submit">Reset</button>
    </form>
</div>
</body>
</html>
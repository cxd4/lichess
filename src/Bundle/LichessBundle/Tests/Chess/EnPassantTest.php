<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Tests\TestManipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Document\Game;
use ArrayObject;

class EnPassantTest extends \PHPUnit_Framework_TestCase
{
    protected $game;

    public function testEnPassant()
    {
        $data = <<<EOF
       k


pP



K
EOF;
        $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('a5')->setFirstMove($this->game->getTurns()-1);
        $this->game->getBoard()->getPieceByKey('b5')->setFirstMove(0);
        $this->assertMoves('b5', 'b6 a6');
        $this->move('b5 a6');
        $this->assertTrue($this->game->getBoard()->getPieceByKey('a6')->isClass('Pawn'));
        $this->assertTrue($this->game->getBoard()->getSquareByKey('b5')->isEmpty());
        $this->assertTrue($this->game->getBoard()->getSquareByKey('a5')->isEmpty());
    }

    public function testEnPassantTooLate()
    {
        $this->createGame();
        $this->move('d2 d4');
        $this->move('c7 c5');
        $this->move('d4 d5');

        $pieceKey = 'c5';
        $moves = array('c4');
        $possibleMoves = $this->analyser->getPlayerPossibleMoves($this->game->getTurnPlayer());
        $this->assertEquals($moves, isset($possibleMoves[$pieceKey]) ? $this->sort($possibleMoves[$pieceKey]) : null);
    }

    public function testEnPassantImpossibleWhenOneSquareOnly()
    {
        $data = <<<EOF
       k

pP




K
EOF;
        $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('a6')->setFirstMove($this->game->getTurns()-1);
        $this->game->getBoard()->getPieceByKey('b6')->setFirstMove(0);
        $this->assertMoves('b6', 'b7');
    }

    public function testEnPassantCanSaveTheKing()
    {
        $data = <<<EOF
    r  k

   p
  pP b
  PK

  r

EOF;
        $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('c5')->setFirstMove($this->game->getTurns()-1);
        // King can not move
        $this->assertMoves('d4', '');
        // En passant is possible
        $this->assertMoves('d5', 'c6');
    }

    /**
     * Moves a piece and increment game turns
     *
     * @return void
     **/
    protected function move($move, array $options = array())
    {
        $manipulator = new TestManipulator($this->game, new ArrayObject());
        $manipulator->move($move, $options);
        $this->game->getBoard()->compile();
        $this->game->addTurn();
    }

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data = null, $blackTurn = false)
    {
        $generator = new Generator();
        if ($data) {
            $this->game = $generator->createGameFromVisualBlock($data);
            $this->game->setTurns($blackTurn ? 11 : 10);
        }
        else {
            $this->game = $generator->createGame();
        }
        $this->game->setStatus(Game::STARTED);
        $this->analyser = new Analyser($this->game->getBoard());
    }

    protected function assertMoves($pieceKey, $moves)
    {
        $moves = empty($moves) ? null : $this->sort(explode(' ', $moves));
        $possibleMoves = $this->analyser->getPlayerPossibleMoves($this->game->getTurnPlayer());
        $this->assertEquals($moves, isset($possibleMoves[$pieceKey]) ? $this->sort($possibleMoves[$pieceKey]) : null);

        //$piecePossibleMoves = $this->analyser->getPiecePossibleMoves($this->game->getBoard()->getPieceByKey($pieceKey));
        //$this->assertEquals($moves, $this->sort($piecePossibleMoves));
    }

    protected function sort($array)
    {
        sort($array);
        return $array;
    }
}

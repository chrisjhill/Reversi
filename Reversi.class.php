<?php
class Reversi
{
    /**
     * How big is our grid? Minimum of 9, maximum of 19.
     *
     * @var int
     * @access private
     */
    private $_gridSize;
    
    /**
     * The board in array form. Each Y is a an array containing an array of X.
     *
     * @var array
     * @access private
     */
    private $_boardContent;
    
    /**
     * The board string after a turn has been played.
     *
     * @var string
     * @access private
     */
    private $_boardContentAfterTurn;
    
    /**
     * The person who has just played this move
     *
     * @var string
     * @access private
     */
    private $_turnInPlay;
    
    /**
     * The X coord that is being played.
     *
     * @var mixed
     * @access private
     */
    private $_x = false;
    
    /**
     * The Y coord that is being played.
     *
     * @var mixed
     * @access private
     */
    private $_y = false;
    
    /**
     * How many disks were flipped?
     *
     * @var bool
     * @access private
     */
    private $_disksFlipped = 0;
    
    /**
     * Set the size of the grid, prepare the board and make a move.
     *
     * @param $gridSize int 
     * @access public
     */
    public function __construct($gridSize) {
        // Setup the board
        $this->setGridSize($gridSize);
        $this->setCoords();
        $this->setBoardString();
        $this->setTurn();
        $this->setBoardContent();
        
        // Try and process the move
        $this->doTurn();
        
        // Cleanup the move just played
        $this->doCleanup();
    }
    
    /**
     * Set the grid size.
     *
     * @param $gridSize int
     * @access private
     */
    private function setGridSize($gridSize) {
        // Set the grid size
        $this->_gridSize = (int)$gridSize;
        
        // Is it too big or too small?
        if ($this->_gridSize < 8) {
            // Too small
            $this->_gridSize = 8;
        } else if ($this->_gridSize > 24) {
            // Too big
            $this->_gridSize = 24;
        }
    }
    
    /**
     * Set the coords that the player wishes to play.
     *
     * @access private
     */
    private function setCoords() {
        // X coord
        if (isset($_GET['x'])) {
            $this->_x = $_GET['x'];
        }
        
        // Y coord
        if (isset($_GET['y'])) {
            $this->_y = $_GET['y'];
        }
    }
    
    /**
     * Set the board string.
     *
     * @access private
     */
    private function setBoardString() {
        // Do we have a board to use already?
        if (isset($_GET['board'])) {
            // Yes, use that
            $this->_boardContent = $_GET['board'];
        } else {
            // No, create a fresh board
            $this->_boardContent = str_repeat('-', ($this->_gridSize * $this->_gridSize));

            // Set the default pieces in the center of the board
            $startX = ($this->_gridSize / 2) - 1;
            $startX = ($startX * $this->_gridSize) + $startX;
            $this->_boardContent = substr_replace($this->_boardContent, 'wb', $startX, 2);
            $this->_boardContent = substr_replace($this->_boardContent, 'bw', ($startX + $this->_gridSize), 2);
        }        
    }
    
    /**
     * Sets which player is currently playing, and which is playing after this turn.
     *
     * Note: We think the color is the person who has *just* gone. At the start this
     * will be white (as they will have placed the last starting disk), which means
     * it is theor turn with black to go next.
     *
     * @access private
     */
    private function setTurn() {
        // Set to black if there is no turn set, or it is blacks turn
        $this->_turnInPlay = ! isset($_GET['turn']) || $_GET['turn'] == 'b'
            ? 'b'
            : 'w';
    }
    
    /**
     * Use the board string to create the grid.
     *
     * @access private
     */
    public function setBoardContent() {
        // Set the board string encase no move is made
        $this->_boardContentAfterTurn = $this->_boardContent;

        // Split string into valid X coord lengths
        $this->_boardContent = str_split($this->_boardContent, $this->_gridSize);
        
        // Loop over each Y coord...
        foreach ($this->_boardContent as $index => $line) {
            // ... and insert each X coord
            $this->_boardContent[$index] = str_split($this->_boardContent[$index], 1);
        }
    }
    
    /**
     * Start the turn.
     *
     * 1. Need to make sure the coords are set.
     * 2. Are the coords valid, or are they outside the grid?
     * 3. Make sure the disk is empty.
     *
     * if those conditions are met then we can continue with the move.
     * Set the disk first, then start the traversing from top to top-left.
     *
     * @access public
     */
    public function doTurn() {
        // Do we need to make a move?
        if ($this->_x === false || $this->_y === false) {
            return false;
        }
        
        // Are the coords valid?
        else if (! isset($this->_boardContent[$this->_y][$this->_x])) {
            return false;
        }
        
        // Is there already a disk in this coord?
        else if ($this->_boardContent[$this->_y][$this->_x] != '-') {
            return false;
        }

        // Place the users disk on the board
        $this->_boardContent[$this->_y][$this->_x] = $this->_turnInPlay;
        
        // Did we take any of our opponants disks?
        $this->doTraverse(0, -1);  // Top
        $this->doTraverse(1, -1);  // Top right
        $this->doTraverse(1, 0);   // Right
        $this->doTraverse(1, 1);   // Bottom right
        $this->doTraverse(0, 1);   // Bottom
        $this->doTraverse(-1, 1);  // Bottom left
        $this->doTraverse(-1, 0);  // Left
        $this->doTraverse(-1, -1); // Top left
    }
    
    /**
     * Traverse the board to see if we can take any of our opponants disks.
     *
     * To traverse the board we can add, substract, or do nothing with each X and Y coord.
     * Keep traversing until we reach an empty position, a wall, or our own disk. Once
     * reached an end, traverse back down the coords replacing the disks with our own.
     *
     * @param $xDiff int
     * @param $yDiff int
     * @access private
     */
    public function doTraverse($xDiff, $yDiff) {
        // Set variables
        $x = $this->_x;
        $y = $this->_y;
        $continue = true;
        
        // Begin the loop
        do {
            // Work out the new coords to test
            $x += $xDiff;
            $y += $yDiff;
            
            // What is in the next position?
            $next = isset($this->_boardContent[$y][$x])
                ? $this->_boardContent[$y][$x]
                : 'e'; // Edge

            // Have we hit an edge or an empty position?
            if ($next == 'e' || $next == '-') {
                $continue = false;
            }
            
            // Have we reached our own disk colour?
            else if ($next == $this->_turnInPlay) {
                // We are currently at our own disk, move back one so we are at our
                // .. last free (potentially) disk.
                if ($xDiff > 0) { $x--; } else if ($xDiff < 0) { $x++; }
                if ($yDiff > 0) { $y--; } else if ($yDiff < 0) { $y++; }
                
                // Are we where we started?
                while ($x != $this->_x || $y != $this->_y) {
                    // Change this disk to the player who just moved
                    $this->_boardContent[$y][$x] = $this->_turnInPlay;
                    
                    // Set the number of disks this flipped
                    $this->_disksFlipped++;
                    
                    // Move back one coord to begin another replacement
                    if ($xDiff > 0) { $x--; } else if ($xDiff < 0) { $x++; }
                    if ($yDiff > 0) { $y--; } else if ($yDiff < 0) { $y++; }
                }
                
                // We have converted all of the possible disks, exit the traverse
                $continue = false;
            }
        } while ($continue);
    }
    
    /**
     * We have finished the turn, so need to run some post-turn sanity checks.
     * 
     * If there were no disks flipped then it was an invalid turn - the same
     * colour that just went needs to go again.
     *
     * If it was an invalid move then we need to replace the just-set-disk with
     * an empty disk so it doesn't become a permanent fixture on the board.
     *
     * The board is now valid, create the board string for the empty disk links.
     *
     * @access private
     */
    private function doCleanup() {
        // Did we actually flip any disks (if we did then it must be valid)
        if ($this->_disksFlipped >= 1) {
            $this->_turnInPlay = $this->_turnInPlay == 'b'
                ? 'w'
                : 'b';
        }
        
        // Were the coords set, but was an invalid move?
        else if (! $this->getIsValidMove()) {
            // Reset the disk
            $this->_boardContent[$this->_y][$this->_x] = '-';
        }
        
        // All moves have finished, save the board string
        $this->_boardContentAfterTurn = $this->getBoardAfterTurn();
    }
    
    /**
     * Was the move valid?
     *
     * Was only invalid if coords were set and no disks were flipped.
     * 
     * @access private
     * @return boolean
     */
    private function getIsValidMove() {
        // If the user made a move and the disks flipped were none
        return $this->_x && $this->_disksFlipped <= 0
            ? false
            : true;
    }

    /**
     * Compress the board down into a string.
     *
     * @access public
     * @return string
     */
    public function getBoardAfterTurn() {
        $board = '';
        for ($y = 0; $y < $this->_gridSize; $y++) {
            $board .= implode($this->_boardContent[$y], '');
        }
        return $board;
    }

    /**
     * Output the board after the move.
     *
     * @access private
     */
    public function getBoard() {
        // Start output
        $output = '<tr><td class="board-corner">&nbsp;</td>';
        
        // Set each top row
        $letter = 'a';
        for ($x = 0; $x < $this->_gridSize; $x++) {
            $output .= '<th>' . strtoupper($letter++) . '</th>';
        }
        
        // End the top row
        $output .= '</tr>';
        
        // Loop through each Y coord
        for ($y = 0; $y < $this->_gridSize; $y++) {
            // Start the row
            $output .= '<tr><th>' . ($y + 1) . '</th>';
            
            // Loop through each X coord            
            for ($x = 0; $x < $this->_gridSize; $x++) {
                // Which disk do we need to place?
                switch ($this->_boardContent[$y][$x]) {
                    case 'b' : $output .= '<td><img src="disk-b.png" alt="B" class="disk-black" rel="'.$x.':'.$y.'" /></td>'; break;
                    case 'w' : $output .= '<td><img src="disk-w.png" alt="W" class="disk-white" rel="'.$x.':'.$y.'" /></td>'; break;
                    default  : $output .= '<td><a href="?x=' . $x . '&y=' . $y . '&turn=' . $this->_turnInPlay . '&board=' . $this->_boardContentAfterTurn . '" class="disk-empty" rel="'.$x.':'.$y.'"><img src="disk-e.png" alt="" /></a></td>';
                }
            }
            
            // End the row
            $output .= '</tr>';
        }
        
        // Return the output
        return $output;
    }
    
    /**
     * Get the player playing now.
     *
     * @access public
     * @return string
     */
    public function getTurn() {
        return $this->_turnInPlay;
    }
    
    /**
     * Returns the scores and empty disks.
     * 
     * <code>
     * Array(
     *     ['white'] => 123,
     *     ['black'] => 321,
     *     ['empty'] => 213
     * )
     * </code>
     * 
     * @access public
     * @return array
     */
    public function getScore() {
        // Get black and white
        $whiteCount = substr_count($this->_boardContentAfterTurn, 'w');
        $blackCount = substr_count($this->_boardContentAfterTurn, 'b');
        
        // Return scores
        return array(
            'white' => $whiteCount,
            'black' => $blackCount,
            'empty' => ($this->_gridSize * $this->_gridSize) - ($whiteCount + $blackCount)
        );
    }
    
    /**
     * Who is winning? Is it a tie?
     *
     * @access public
     * @return string
     */
    public function getGameStatus() {
        // Get the stats
        $stats = $this->getScore();
        
        // Is black winning?
        if ($stats['black'] > $stats['white']) {
            return 'b';
        }
        
        // Is white winning?
        else if ($stats['white'] > $stats['black']) {
            return 'w';
        }
        
        // It must be a tie
        return 'tie';
    }

    /**
     * Get how many discs this flipped.
     * 
     * @access public
     * @return int
     */
    public function getDisksFlipped() {
        return $this->_disksFlipped;
    }

    /**
     * Returns the full name of a colour
     * 
     * @access public
     * @param $uppercaseFirstLetter boolean
     * @return string
     */
    public function getFullColor($color, $uppercaseFirstLetter = false) {
        // Work out colour
        $color = $color == 'w' ? 'white' : 'black';
        
        // And return
        return $uppercaseFirstLetter
            ? ucfirst($color)
            : $color;
    }
}
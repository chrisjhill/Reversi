<?php
// Quick settings
set_time_limit(2);
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Include the Reversi class
include 'libs/Core/Reversi.class.php';

// How big (wide and tall) should the playing board be?
$gridSize = 8;

// Create the game!
$reversi = new Core_Reversi($gridSize);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Language" content="en-gb" />
    <title>Reversi board game in PHP and Javascript</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    
    <link rel="stylesheet" type="text/css" href="./assets/css/reversi.css" media="screen" />
    
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="./assets/js/reversi.js"></script>
    <script type="text/javascript" src="./assets/js/str_split.js"></script>
    <script type="text/javascript">
    // Set variables
    var gridSize           = <?php echo $gridSize; ?>,
        boardContent       = new Object,
        boardContentString = "<?php echo $reversi->getBoardAfterTurn(); ?>",
        turnInPlay         = "<?php echo $reversi->getTurn(); ?>",
        turnNext           = turnInPlay == 'b' ? 'w' : 'b',
        coords             = null,
        x                  = false,
        y                  = false,
        xTemp              = false,
        yTemp              = false,
        next               = false,
        continueOn         = true,
        disksChanged       = new Array;
        
    // Setup the board
    setBoardContent();
    </script>
</head>
<body>
    <div style="float:left">
        <table id="board">
            <?php echo $reversi->getBoard(); ?>
        </table>
    </div>
    
    <div style="float:left;margin-left:30px">
        <h1>Reversi stats</h1>
        
        <!-- Set the stats //-->
        <?php
        // Get the stats
        $reversiScore  = $reversi->getScore();
        $reversiStatus = $reversi->getGameStatus();
        ?>
        
        <!-- Is the game still ongoing? //-->
        <?php if ($reversiScore['empty'] <= 0) { ?>
            <!-- Game has finished //-->
            <p><strong>The game has finished!</strong></p>
            
            <p>
                <strong><?php echo $reversi->getFullColor($reversiStatus, true); ?>'s</strong> win,
                the score being
                <strong><?php echo $reversiScore['white']; ?></strong>-<strong><?php echo $reversiScore['black']; ?></strong>
            </p>
            
            <p><a href="/">Play again, why not?!</a></p>
        <?php } else { ?>
            <!-- Game is in progress //-->
            <!-- Is it a tie? //-->
            <?php if ($reversiStatus == 'tie') { ?>
                <!-- Tie //-->
                <p>
                    <strong>It's a tie!</strong>
                    <strong><?php echo $reversiScore['white']; ?></strong>-<strong><?php echo $reversiScore['black']; ?></strong>
                    with <strong><?php echo $reversiScore['empty']; ?></strong> disks left to play.
                </p>
            <?php } else { ?>
                <!-- Someone is winning //-->
                <p>
                    <strong><?php echo $reversi->getFullColor($reversiStatus, true); ?>'s</strong> are winning,
                    <strong><?php echo $reversiScore['white']; ?></strong>-<strong><?php echo $reversiScore['black']; ?></strong>
                    with <strong><?php echo $reversiScore['empty']; ?></strong> disks left to play.
                </p>
            <?php } ?>
            
            <!-- Which players turn is it? //-->
            <p><strong><?php echo $reversi->getFullColor($reversi->getTurn(), true); ?></strong>, it is your turn to play a disk!</p>
            
            <!-- How many disks were flipped? //-->
            <?php if ($reversi->getDisksFlipped() >= 1) { ?>
                <!-- Some were flipped //-->
                <p><?php echo $reversi->getDisksFlipped(); ?> disks were flipped!</p>
            <?php } else if (isset($_GET['x'])) { ?>
                <!-- No disks were flipped //-->
                <div class="error">
                    <p>You didn't flip any disks! If you wish to skip your go then <a href="/?=<?php echo (int)$_GET['x']; ?>&y=<?php echo (int)$_GET['x']; ?>&turn=<?php echo $_GET['turn'] == 'b' ? 'w' : 'b'; ?>&board=<?php echo htmlentities($_GET['board']); ?>">click here</a>.</p>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</body>
</html>
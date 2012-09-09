// Set the board from the board string
function setBoardContent() {        
    // Split string into valid X coord lengths
    boardContent = str_split(boardContentString, gridSize);
    
    // Loop over each Y coord...
    for (var i = 0; i < gridSize; i++) {
        // ... and insert each X coord
        boardContent[i] = str_split(boardContent[i], 1);
    }
}

// Try and place a disk on the board
function doTurn() {
    // is there already a disk in this coord?
    if (boardContent[y][x] != '-') {
        return false;
    }

    // Place the users disk on the board
    boardContent[y][x] = turnInPlay;
    
    // Did we take any of our opponants disks?
    doTraverse(0, -1);  // Top
    doTraverse(1, -1);  // Top right
    doTraverse(1, 0);   // Right
    doTraverse(1, 1);   // Bottom right
    doTraverse(0, 1);   // Bottom
    doTraverse(-1, 1);  // Bottom left
    doTraverse(-1, 0);  // Left
    doTraverse(-1, -1); // Top left
}

// Begin the reversing of the disk
function doTraverse(xDiff, yDiff) {
    // Set variables
    xTemp = x;
    yTemp = y;
    continueOn = true;
    
    // Begin the loop
    do {
        // Work out the new coords to test
        xTemp += xDiff;
        yTemp += yDiff;

        // What is in the next position?
        next = typeof boardContent[yTemp] != "undefined" && typeof boardContent[yTemp][xTemp] != "undefined"
            ? boardContent[yTemp][xTemp]
            : 'e'; // Edge

        // Have we hit an edge or an empty position?
        if (next == 'e' || next == '-') {
            continueOn = false;
        }

        // Have we reached our own disk colour?
        else if (next == turnInPlay) {
            // We are currently at our own disk, move back one so we are at our
            // .. last free (potentially) disk.
            if (xDiff > 0) { xTemp--; } else if (xDiff < 0) { xTemp++; }
            if (yDiff > 0) { yTemp--; } else if (yDiff < 0) { yTemp++; }
            
            // Are we where we started?
            while (xTemp != x || yTemp != y) {
                // Change this disk to the player who just moved
                boardContent[yTemp][xTemp] = turnInPlay;
                
                // Change the image
                $("img[rel='"+xTemp+":"+yTemp+"']").attr("src", "./assets/img/disk-"+turnInPlay+".png");
                
                // Set which disk we just updated
                disksChanged[disksChanged.length] = [xTemp, yTemp];

                // Move back one coord to begin another replacement
                if (xDiff > 0) { xTemp--; } else if (xDiff < 0) { xTemp++; }
                if (yDiff > 0) { yTemp--; } else if (yDiff < 0) { yTemp++; }
            }
            
            // We have converted all of the possible disks, exit the traverse
            continueOn = false;
        }
    } while (continueOn);
}

// When we hover away from an empty disk we need to reset the board
function resetDisks() {
    // Change the empty disk back to empty
    boardContent[y][x] = "-";
    
    // Set variables
    var disksChangedLength = disksChanged.length;
    
    // Loop over changed disks
    for (var i = 0; i < disksChangedLength; i++) {
        // Reset disk image
        $("img[rel='"+disksChanged[i][0]+":"+disksChanged[i][1]+"']").attr("src", "./assets/img/disk-"+turnNext+".png");
        
        // Reset the board
        boardContent[disksChanged[i][1]][disksChanged[i][0]] = turnNext;
    }
    
    // And reset the discs changed
    disksChanged = new Array;
}

// The DOM is ready
$(document).ready(function() {
    // Wait for the player to mouseover an empty disc
    $(".disk-empty").mouseenter(function() {
        // Set the disk colour
        $("img", this).attr("src", "./assets/img/disk-"+turnInPlay+".png");
        
        // Set the X and Y coords
        coords = $(this).attr("rel");
        coords = coords.split(':');
        x      = parseInt(coords[0]);
        y      = parseInt(coords[1]);
        
        // Do turn
        doTurn();
    }).mouseleave(function() {
        // Reset the disk that the user hovered over
        $("img", this).attr("src", "./assets/img/disk-e.png");
        
        // Reset the disks we changed
        resetDisks();
    });
});
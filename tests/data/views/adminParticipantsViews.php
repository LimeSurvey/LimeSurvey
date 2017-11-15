<?php

/**
 * This contains a list of survey-related admin views that we can loop for testing
 */
return [

    // Central participants DB ---------------------------------------
    // --------------------------------------------------

    ['displayParticipants', ['route'=>'participants/sa/displayParticipants']],
    ['participantsSummary', ['route'=>'participants/sa/index']],
    ['importParticipants', ['route'=>'participants/sa/importCSV']],
    ['participantsBlacklistControl', ['route'=>'participants/sa/blacklistControl']],
    ['participantsAttributeControl', ['route'=>'participants/sa/attributeControl']],
    ['participantsSharePanel', ['route'=>'participants/sa/sharePanel']],


];
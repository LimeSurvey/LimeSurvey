CREATE TABLE tokens_59864 ( 
    `tid`          	int(11) AUTO_INCREMENT NOT NULL,
    `firstname`    	varchar(40) NULL,
    `lastname`     	varchar(40) NULL,
    `email`        	text NULL,
    `emailstatus`  	text NULL,
    `token`        	varchar(36) NULL,
    `language`     	varchar(25) NULL,
    `sent`         	varchar(17) NULL DEFAULT 'N',
    `remindersent` 	varchar(17) NULL DEFAULT 'N',
    `remindercount`	int(11) NULL DEFAULT '0',
    `completed`    	varchar(17) NULL DEFAULT 'N',
    `validfrom`    	datetime NULL,
    `validuntil`   	datetime NULL,
    `mpid`         	int(11) NULL,
    PRIMARY KEY(tid)
);

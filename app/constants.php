<?php

/*
 * To access:
 *     require_once(app_path().'/constants.php');
 *     ...
 *     CELL_CONFIRMATION_LENGTH
 */

    define("EMAIL_CONFIRMATION_LENGTH",                                                        20 );
    define("EMAIL_MAX_LENGTH",                                                                128 );
    define("USERNAME_MAX_LENGTH",                                                             128 );
    define("PASSWORD_LENGTH",                                                                  60 );
    
    define("MAX_LOGIN_ATTEMPTS",                                                                5 );
    // MS - 2 hours
    define("LOCK_TIME",                                                                    7200000);

    define("LOGIN_TOKEN_EXPIRATION",                                                         null );

    define("ACCOUNT_STATUS_UNCONFIRMED",                                                        0 );
    define("ACCOUNT_STATUS_CONFIRMED",                                                          1 );
    define("ACCOUNT_STATUS_PREMIUM",                                                            2 );
    define("ACCOUNT_STATUS_PREV_PREMIUM",                                                       3 );
    define("ACCOUNT_STATUS_CANCELLED",                                                          4 );
    define("ACCOUNT_STATUS_BANNED",                                                             5 );
    define("ACCOUNT_STATUS_ADMIN",                                                              6 );
    define("ACCOUNT_STATUSES",              ["unconfirmed","ok","premium","ok","cancelled","banned",
                                             "admin"]);

    define("VOLUMES",                                          ["", "ot", "nt", "bm", "dc", "pgp"]);
    define("BOOK_ID_OFFSETS",                                                    [0,1,40,67,82,83]);
    define("BOOKS",                         [[],["gen", "ex", "lev", "num", "deut", "josh", "judg", 
        "ruth", "1_sam", "2_sam", "1_kgs", "2_kgs", "1_chr", "2_chr", "ezra", "neh", "esth", "job", 
        "ps", "prov", "eccl", "song", "isa", "jer", "lam", "ezek", "dan", "hosea", "joel", "amos", 
        "obad", "jonah", "micah", "nahum", "hab", "zeph", "hag", "zech", "mal"],["matt", "mark", 
        "luke", "john", "acts", "rom", "1_cor", "2_cor", "gal", "eph", "philip", "col", "1_thes", 
        "2_thes", "1_tim", "2_tim", "titus", "philem", "heb", "james", "1_pet", "2_pet", "1_jn", 
        "2_jn", "3_jn", "jude", "rev"],["1_ne", "2_ne", "jacob", "enos", "jarom", "omni", "w_of_m",
        "mosiah", "alma", "hel", "3_ne", "4_ne", "morm", "ether", "moro"],["dc"],["moses", "abr",
        "js_m", "js_h", "a_of_f"]]);
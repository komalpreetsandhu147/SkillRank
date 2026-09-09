<?php
const DB_HOST = '127.0.0.1';
const DB_NAME = 'skillrank';
const DB_USER = 'root';
const DB_PASS = '';
const APP_NAME = 'SkillRank';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

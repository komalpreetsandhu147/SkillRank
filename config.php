<?php
// SkillRank System Configuration
const DB_HOST = '127.0.0.1';
const DB_NAME = 'skillrank';
const DB_USER = 'root';
const DB_PASS = '';
const APP_NAME = 'SkillRank';

// Google Gemini API Key for Generative AI Features
// Leave blank to run in fast smart algorithmic mode, or enter your key from Google AI Studio.
defined('GEMINI_API_KEY') or define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<?php
/**
 * Language Helper Functions
 */

// Load language file
function loadLanguage($lang = null) {
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }
    
    $langFile = __DIR__ . '/../languages/' . $lang . '.php';
    
    if (file_exists($langFile)) {
        return include $langFile;
    }
    
    // Fallback to Kurdish if language not found
    return include __DIR__ . '/../languages/ku.php';
}

// Get translation
function __($key, $lang = null) {
    static $translations = [];
    
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }
    
    if (!isset($translations[$lang])) {
        $translations[$lang] = loadLanguage($lang);
    }
    
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}

// Translate with variables
function __t($key, $vars = [], $lang = null) {
    $text = __($key, $lang);
    
    foreach ($vars as $placeholder => $value) {
        $text = str_replace('{' . $placeholder . '}', $value, $text);
    }
    
    return $text;
}

// Get all translations for current language
function getAllTranslations($lang = null) {
    return loadLanguage($lang);
}

// Set language direction (RTL or LTR)
function getLanguageDirection($lang = null) {
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }
    
    return in_array($lang, ['ar', 'ku']) ? 'rtl' : 'ltr';
}

// Get language name
function getLanguageName($code) {
    $languages = [
        'en' => 'English',
        'ku' => 'کوردی',
        'ar' => 'عربي'
    ];
    
    return isset($languages[$code]) ? $languages[$code] : $code;
}

// Get available languages
function getAvailableLanguages() {
    return [
        'en' => 'English',
        'ku' => 'کوردی',
        'ar' => 'عربي'
    ];
}

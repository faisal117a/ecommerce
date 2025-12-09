<?php
// Site Settings Helper Functions

require_once __DIR__ . '/database.php';

/**
 * Get a site setting value
 */
function getSetting(string $key, string $default = ''): string {
    static $settings = null;
    
    if ($settings === null) {
        $pdo = getDB();
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Set a site setting value
 */
function setSetting(string $key, string $value, string $type = 'text'): bool {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_type) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?, setting_type = ?");
    return $stmt->execute([$key, $value, $type, $value, $type]);
}

/**
 * Get all settings as an array
 */
function getAllSettings(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT setting_key, setting_value, setting_type FROM site_settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = [
            'value' => $row['setting_value'],
            'type' => $row['setting_type']
        ];
    }
    return $settings;
}

/**
 * Save multiple settings at once
 */
function saveSettings(array $settings): bool {
    $pdo = getDB();
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_type) 
                               VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE setting_value = ?, setting_type = ?");
        
        foreach ($settings as $key => $data) {
            $value = is_array($data) ? ($data['value'] ?? '') : $data;
            $type = is_array($data) ? ($data['type'] ?? 'text') : 'text';
            $stmt->execute([$key, $value, $type, $value, $type]);
        }
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Settings save error: " . $e->getMessage());
        return false;
    }
}


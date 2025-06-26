<?php
require_once 'includes/db.php';

try {
    // テーブル一覧を取得
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h2>データベース接続成功！</h2>";
    echo "<h3>作成されたテーブル:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";

    // ユーザー数を確認
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "<p>登録ユーザー数: " . $userCount . "人</p>";
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}

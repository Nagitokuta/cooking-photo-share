# 料理写真共有プラットフォーム

ユーザーが料理の写真を投稿し、他のユーザーと共有できるWebプラットフォームです。

## 機能一覧

### 基本機能
- **ユーザー登録・認証**: 新規登録、ログイン、ログアウト
- **写真投稿**: 料理写真のアップロードと説明文の投稿
- **タイムライン**: 他ユーザーの投稿を時系列で表示
- **いいね機能**: 写真に対するいいねの追加・削除
- **コメント機能**: 写真に対するコメントの投稿・削除
- **マイページ**: プロフィール管理と自分の投稿一覧

### 技術仕様
- **フロントエンド**: HTML5, CSS3, JavaScript (ES6+)
- **バックエンド**: PHP 7.4+
- **データベース**: MySQL 5.7+
- **画像処理**: GD Extension
- **セッション管理**: PHP Sessions

## セットアップ手順

### 1. 環境要件
- PHP 7.4以上
- MySQL 5.7以上
- Apache/Nginx
- GD Extension

### 2. データベースセットアップ
```sql
-- データベース作成
CREATE DATABASE cooking_photos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- SQLファイルを実行
mysql -u username -p cooking_photos_db < sql/database.sql
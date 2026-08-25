# my-simple-calendar
WordPress Plugin - simply events calendar!

=== My Simple Calendar ===
Contributors: Onozaki Masashi (Garage034)
Tags: calendar, events, shortcode, color-picker
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 5.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

イベント無制限・カラーピッカー対応・イベント別形状選択機能付きの、軽快でスタイリッシュなカレンダープラグインです。

== Description ==

My Simple Calendar は、WordPressサイトに
カスタマイズ性の高いイベントカレンダーを簡単に設置できるプラグインです。

重いライブラリを排除した軽量設計により、サイトの表示速度を落とさずスムーズに動作します。


== Features (機能) ==

* **イベント無制限登録**: 
用途に合わせてイベントの種類をいくつでも自由に作成できます。

* **イベント別形状選択**: 
イベントごとに日付の形（「丸」「角丸」「三角」）を個別に設定可能です。

* **カラーピッカー対応**: 
直感的なカラー選択で、ブランドカラーやサイトのデザインに合わせた配色が可能です。

* **ポップアップ詳細表示**: 
カレンダーの日付をクリック（またはタップ）すると、イベントの詳細や時間情報をポップアップでスマートに表示します。

* **個別時間設定**: 
日付ごとに個別の時間を上書き設定できます。

* **グラデーション対応**: 
1日に複数のイベントが重なった場合、美しいグラデーションで表現されます。

* **軽量・高速設計**: 
余計なスクリプトを読み込まず、サイトのパフォーマンスに配慮した設計になっています。

== Installation (設置の仕方) ==

1. プラグインのZIPファイルをWordPress管理画面の「プラグイン」＞「新規追加」＞「プラグインのアップロード」からアップロードするか、サーバーの `wp-content/plugins/` ディレクトリにフォルダごとアップロードします。
2. WordPress管理画面の「プラグイン」メニューから「My Simple Calendar Plus」を有効化します。
3. 管理画面のメニューに追加される **「📅 カレンダー設定」** から、イベントの種類、色、形状などを設定します。
4. カレンダーを表示させたい固定ページや投稿、またはテンプレートファイルに以下のショートコードを挿入します。
   `[my_calendar]`
5. テンプレートファイル（PHP）内に直接埋め込む場合は、以下のコードを使用してください。
   `<?php echo do_shortcode( '[my_calendar]' ); ?>`

== Frequently Asked Questions ==

= ショートコードはどこに入力すればいいですか？ =
通常の固定ページや投稿のブロックエディターで「ショートコード」ブロックを追加し、`[my_calendar]` と入力してください。

= カレンダーの背景を透けさせることはできますか？ =
CSSの `backdrop-filter` や半透明の `rgba` を用いることで、ヒーローセクションの背景画像に美しく馴染むすりガラス調のデザインにカスタマイズ可能です。

== Changelog ==

= 5.3 =
* イベントごとの形状選択機能（丸・角丸・三角）を追加しました。
* フロントエンドおよび管理画面の表示を最適化しました。

= 5.2 =
* カレンダー全体の形状選択機能を追加しました。

= 5.0 =
* 初回リリース。カラーピッカー、ポップアップ、無制限イベント対応。

== Copyright (著作権・ライセンス) ==

My Simple Calendar Plus is copyright (c) 2026 Onozaki Masashi [Design Studio Garage034.com], distributed under the terms of the GNU General Public License (GPL).

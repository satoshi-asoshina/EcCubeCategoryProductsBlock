$(function() {
    // スクロール矢印ボタンの追加と制御
    $('.ec-maker-block__carousel').each(function() {
        var carousel = $(this);
        var visibleCount = carousel.data('visible-count') || 4;
        var visibleCountSp = carousel.data('visible-count-sp') || 1;
        
        // 矢印ボタンを追加（アイコンフォントがない場合は文字で代用）
        var prevBtn = $('<button class="ec-maker-block__arrow ec-maker-block__arrow--prev">&lt;</button>');
        var nextBtn = $('<button class="ec-maker-block__arrow ec-maker-block__arrow--next">&gt;</button>');
        
        // 親要素に追加
        carousel.parent().append(prevBtn);
        carousel.parent().append(nextBtn);
        
        // アイテムとスクロール情報
        var items = carousel.find('.ec-maker-block__item');
        var itemCount = items.length;
        
        // 各アイテムのインデックスを保存
        items.each(function(index) {
            $(this).attr('data-index', index);
        });
        
        // 現在の表示位置を追跡
        var currentPosition = 0;
        
        // ウィンドウサイズに応じた表示数を計算
        function getVisibleItemCount() {
            var winWidth = $(window).width();
            if (winWidth <= 576) {
                return 1; // スマホは1つ表示
            } else if (winWidth <= 768) {
                return 2; // 小さめのタブレットは2つ表示
            } else if (winWidth <= 992) {
                return 3; // タブレットは3つ表示
            } else {
                return visibleCount; // PCはvisibleCountで指定された数
            }
        }
        
        // カルーセルを特定の位置に移動
        function scrollToPosition(position) {
            // 範囲を正規化（循環対応）
            if (position < 0) {
                position = itemCount - getVisibleItemCount();
            } else if (position > itemCount - getVisibleItemCount()) {
                position = 0;
            }
            
            // 位置を更新
            currentPosition = position;
            
            // ターゲットとなるアイテムの位置までスクロール
            var targetItem = items.eq(position);
            carousel.animate({
                scrollLeft: targetItem.position().left + carousel.scrollLeft() - carousel.position().left
            }, 300);
        }
        
        // スクロールボタンクリック処理
        prevBtn.on('click', function() {
            scrollToPosition(currentPosition - 1);
        });
        
        nextBtn.on('click', function() {
            scrollToPosition(currentPosition + 1);
        });
        
        // タッチスワイプ対応
        var startX, startScrollLeft;
        
        carousel.on('touchstart', function(e) {
            startX = e.originalEvent.touches[0].pageX;
            startScrollLeft = carousel.scrollLeft();
        });
        
        carousel.on('touchend', function(e) {
            var endX = e.originalEvent.changedTouches[0].pageX;
            var diffX = startX - endX;
            
            // 一定以上のスワイプ距離でスクロール
            if (Math.abs(diffX) > 50) {
                if (diffX > 0) {
                    // 左スワイプ → 次へ
                    scrollToPosition(currentPosition + 1);
                } else {
                    // 右スワイプ → 前へ
                    scrollToPosition(currentPosition - 1);
                }
            }
        });
        
        // 初期位置に設定
        scrollToPosition(0);
        
        // リサイズ時に再計算
        $(window).resize(function() {
            scrollToPosition(currentPosition);
        });
    });
});
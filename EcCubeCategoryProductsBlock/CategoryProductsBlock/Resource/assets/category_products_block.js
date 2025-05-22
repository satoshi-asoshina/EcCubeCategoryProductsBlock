/**
 * カテゴリー商品ブロック用JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // すべてのカテゴリータグブロックに処理を適用
    const categoryBlocks = document.querySelectorAll('.ec-categoryProductsBlock');
    
    if (categoryBlocks.length > 0) {
        categoryBlocks.forEach(function(block) {
            // カテゴリータグの切り替え処理
            const categoryTags = block.querySelectorAll('.ec-categoryTag');
            const productItemsContainer = block.querySelector('.ec-productItems');
            const viewMoreButton = block.querySelector('.ec-viewMoreButton a');
            
            if (categoryTags && productItemsContainer && viewMoreButton) {
                categoryTags.forEach(tag => {
                    tag.addEventListener('click', function() {
                        // アクティブクラスの切り替え
                        categoryTags.forEach(t => t.classList.remove('is-active'));
                        this.classList.add('is-active');
                        
                        const categoryId = this.getAttribute('data-category-id');
                        const blockId = block.getAttribute('id') || '';
                        
                        // ローディング表示
                        productItemsContainer.innerHTML = '<div class="ec-loading"><div class="ec-loading__spinner"></div></div>';
                        
                        // Ajaxリクエストで商品を取得
                        fetch(`${eccube.baseUrl}/block/category_products?category_id=${categoryId}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(html => {
                            productItemsContainer.innerHTML = html;
                            
                            // 「もっと見る」ボタンのリンク先を更新
                            viewMoreButton.href = `${eccube.baseUrl}/products/list?category_id=${categoryId}`;
                            
                            // ボタンのテキストを更新
                            const categoryName = this.textContent.trim();
                            viewMoreButton.textContent = `${categoryName}の商品をもっと見る`;
                        })
                        .catch(error => {
                            console.error('商品データの取得に失敗しました:', error);
                            productItemsContainer.innerHTML = '<div class="ec-emptyMessage"><p>商品データの取得に失敗しました</p></div>';
                        });
                    });
                });
            }
        });
    }
    
    // カテゴリータグのスライダー機能（多数のカテゴリがある場合）
    const categoryTagLists = document.querySelectorAll('.ec-categoryTags');
    
    if (categoryTagLists.length > 0) {
        categoryTagLists.forEach(tagList => {
            // コンテナの幅を取得
            const containerWidth = tagList.offsetWidth;
            // タグの合計幅を計算
            const tags = tagList.querySelectorAll('.ec-categoryTag');
            let totalWidth = 0;
            
            tags.forEach(tag => {
                totalWidth += tag.offsetWidth + parseInt(window.getComputedStyle(tag).marginRight);
            });
            
            // タグの合計幅がコンテナより大きい場合、スクロールボタンを追加
            if (totalWidth > containerWidth) {
                // スクロール用のラッパーを作成
                tagList.classList.add('ec-categoryTags--scrollable');
                
                // 左右のスクロールボタンを追加
                const leftBtn = document.createElement('button');
                leftBtn.className = 'ec-categoryTags__scrollBtn ec-categoryTags__scrollBtn--left';
                leftBtn.innerHTML = '<i class="fa fa-chevron-left"></i>';
                
                const rightBtn = document.createElement('button');
                rightBtn.className = 'ec-categoryTags__scrollBtn ec-categoryTags__scrollBtn--right';
                rightBtn.innerHTML = '<i class="fa fa-chevron-right"></i>';
                
                // ボタンクリック時のスクロール処理
                leftBtn.addEventListener('click', function() {
                    tagList.scrollBy({ left: -100, behavior: 'smooth' });
                });
                
                rightBtn.addEventListener('click', function() {
                    tagList.scrollBy({ left: 100, behavior: 'smooth' });
                });
                
                // ボタンをコンテナに追加
                const parentElement = tagList.parentElement;
                parentElement.insertBefore(leftBtn, tagList);
                parentElement.appendChild(rightBtn);
                
                // スクロール位置に応じてボタンの表示/非表示を切り替え
                tagList.addEventListener('scroll', function() {
                    // 左端に到達したら左ボタンを非表示
                    leftBtn.style.display = tagList.scrollLeft <= 0 ? 'none' : 'block';
                    
                    // 右端に到達したら右ボタンを非表示
                    rightBtn.style.display = 
                        Math.ceil(tagList.scrollLeft + tagList.offsetWidth) >= tagList.scrollWidth 
                        ? 'none' : 'block';
                });
                
                // 初期表示の制御
                leftBtn.style.display = 'none'; // 最初は左端なので左ボタンは非表示
            }
        });
    }
});
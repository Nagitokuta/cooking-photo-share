// フィード画面専用のJavaScript（更新版）

document.addEventListener("DOMContentLoaded", function () {
  const photoFeed = document.getElementById("photoFeed");
  const loadMoreBtn = document.getElementById("loadMoreBtn");
  const pagination = document.getElementById("pagination");
  const emptyState = document.getElementById("emptyState");
  const photoCount = document.getElementById("photoCount");

  let currentPage = 1;
  let isLoading = false;
  let hasMorePhotos = true;

  // 初期読み込み
  loadPhotos();

  // もっと見るボタンのイベント
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", loadMorePhotos);
  }

  // スクロール時のヘッダー効果
  window.addEventListener("scroll", handleScroll);

  // 写真一覧を読み込む関数
  function loadPhotos(page = 1) {
    if (isLoading) return;

    isLoading = true;
    showLoading();

    fetch(`api/get_photos.php?page=${page}&limit=10`)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          if (page === 1) {
            photoFeed.innerHTML = "";
            if (data.photos.length === 0) {
              showEmptyState();
              return;
            }
          }

          // 写真カードを生成・追加
          data.photos.forEach((photo) => {
            const photoCard = createPhotoCard(photo);
            photoFeed.appendChild(photoCard);
          });

          updatePhotoCount(data.total);
          hasMorePhotos = data.hasMore;

          if (hasMorePhotos && page === 1) {
            pagination.style.display = "block";
          } else if (!hasMorePhotos) {
            pagination.style.display = "none";
          }
        } else {
          showError(data.message || "写真の読み込みに失敗しました");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showError("ネットワークエラーが発生しました");
      })
      .finally(() => {
        isLoading = false;
        hideLoading();
      });
  }

  // もっと見るボタンの処理
  function loadMorePhotos() {
    if (!hasMorePhotos || isLoading) return;

    currentPage++;
    loadPhotos(currentPage);
  }

  // 写真カードを生成する関数
  function createPhotoCard(photo) {
    const card = document.createElement("div");
    card.className = "photo-card";
    card.dataset.photoId = photo.id;

    console.log(photo);

    const avatarText = photo.username.charAt(0).toUpperCase();
    const postTime = formatPostTime(photo.created_at);

    card.innerHTML = `
            <div class="card-header">
                <div class="user-info">
                    <div class="user-avatar">${avatarText}</div>
                    <div class="user-details">
                        <h3>${escapeHtml(photo.username)}</h3>
                        <p class="post-time">${postTime}</p>
                    </div>
                </div>
            </div>
            <div class="card-image" onclick="openPhotoModal(${photo.id})">
                <img src="${photo.image_url}" alt="${escapeHtml(
      photo.description || ""
    )}" loading="lazy" 
                     onerror="this.src='assets/images/no-image.png'">
            </div>
            ${
              photo.description
                ? `
                <div class="card-content">
                    <p class="card-description">${escapeHtml(
                      photo.description
                    )}</p>
                </div>
            `
                : ""
            }
            <div class="card-actions">
                <button class="action-btn like-btn ${
                  photo.user_liked ? "liked" : ""
                }" 
                        onclick="toggleLike(${photo.id}, this)">
                    <span>${photo.user_liked ? "❤️" : "🤍"}</span>
                    <span class="like-count">${photo.likes_count}</span>
                </button>
                <button class="action-btn comment-btn" onclick="toggleComments(${
                  photo.id
                })">
                    <span>💬</span>
                    <span class="comment-count">${photo.comments_count}</span>
                </button>
            </div>
            <div class="comments-section" id="comments-${
              photo.id
            }" style="display: none;">
                <div class="comments-list" id="comments-list-${photo.id}">
                    <!-- コメントは動的に読み込み -->
                </div>
                <div class="comment-form">
                    <input type="text" class="comment-input" placeholder="コメントを入力..." 
                           onkeypress="handleCommentKeyPress(event, ${
                             photo.id
                           })">
                    <button class="comment-submit" onclick="submitComment(${
                      photo.id
                    })">投稿</button>
                </div>
            </div>
        `;

    return card;
  }

  // ローディング表示・非表示
  function showLoading() {
    if (currentPage === 1) {
      photoFeed.innerHTML = `
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <p class="loading-text">写真を読み込み中...</p>
                </div>
            `;
    } else {
      // もっと見るボタンのローディング
      const btnText = loadMoreBtn.querySelector(".btn-text");
      const btnLoading = loadMoreBtn.querySelector(".btn-loading");
      if (btnText && btnLoading) {
        btnText.style.display = "none";
        btnLoading.style.display = "inline";
        loadMoreBtn.disabled = true;
      }
    }
  }

  function hideLoading() {
    if (currentPage > 1) {
      // もっと見るボタンのローディング解除
      const btnText = loadMoreBtn.querySelector(".btn-text");
      const btnLoading = loadMoreBtn.querySelector(".btn-loading");
      if (btnText && btnLoading) {
        btnText.style.display = "inline";
        btnLoading.style.display = "none";
        loadMoreBtn.disabled = false;
      }
    }
  }

  // 空の状態を表示
  function showEmptyState() {
    photoFeed.style.display = "none";
    emptyState.style.display = "block";
  }

  // エラー表示
  function showError(message) {
    photoFeed.innerHTML = `
            <div class="error-container" style="text-align: center; padding: 3rem 1rem;">
                <p class="error-message" style="color: #e53e3e; margin-bottom: 1rem;">${message}</p>
                <button class="btn btn-secondary" onclick="location.reload()">再読み込み</button>
            </div>
        `;
  }

  // 写真数を更新
  function updatePhotoCount(total) {
    if (photoCount) {
      photoCount.textContent = `${total}件の投稿`;
    }
  }

  // スクロール時のヘッダー効果
  function handleScroll() {
    const header = document.querySelector(".header");
    if (window.scrollY > 100) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  }

  // 時間をフォーマット
  function formatPostTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;

    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return "たった今";
    if (minutes < 60) return `${minutes}分前`;
    if (hours < 24) return `${hours}時間前`;
    if (days < 7) return `${days}日前`;

    return date.toLocaleDateString("ja-JP");
  }

  // HTMLエスケープ
  function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // グローバル関数として公開
  window.toggleLike = function (photoId, button) {
    console.log("Like toggle for photo:", photoId);
    // 実装は次のステップで
  };

  window.toggleComments = function (photoId) {
    const commentsSection = document.getElementById(`comments-${photoId}`);
    if (commentsSection.style.display === "none") {
      commentsSection.style.display = "block";
      loadComments(photoId);
    } else {
      commentsSection.style.display = "none";
    }
  };

  window.submitComment = function (photoId) {
    console.log("Submit comment for photo:", photoId);
    // 実装は次のステップで
  };

  window.handleCommentKeyPress = function (event, photoId) {
    if (event.key === "Enter") {
      submitComment(photoId);
    }
  };

  window.openPhotoModal = function (photoId) {
    console.log("Open modal for photo:", photoId);
    // 実装は次のステップで
  };

  // コメント読み込み関数
  function loadComments(photoId) {
    const commentsList = document.getElementById(`comments-list-${photoId}`);
    if (!commentsList) return;

    commentsList.innerHTML =
      '<p style="text-align: center; color: #718096;">コメントを読み込み中...</p>';

    fetch(`api/get_photo_detail.php?id=${photoId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.photo.comments) {
          displayComments(photoId, data.photo.comments);
        } else {
          commentsList.innerHTML =
            '<p style="text-align: center; color: #718096;">コメントはありません</p>';
        }
      })
      .catch((error) => {
        console.error("Error loading comments:", error);
        commentsList.innerHTML =
          '<p style="text-align: center; color: #e53e3e;">コメントの読み込みに失敗しました</p>';
      });
  }

  // コメント表示関数
  function displayComments(photoId, comments) {
    const commentsList = document.getElementById(`comments-list-${photoId}`);
    if (!commentsList) return;

    if (comments.length === 0) {
      commentsList.innerHTML =
        '<p style="text-align: center; color: #718096; padding: 1rem;">まだコメントがありません</p>';
      return;
    }

    commentsList.innerHTML = comments
      .map((comment) => {
        const avatarText = comment.username.charAt(0).toUpperCase();
        const commentTime = formatPostTime(comment.created_at);

        return `
                <div class="comment-item" style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <div class="comment-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem; flex-shrink: 0;">
                        ${avatarText}
                    </div>
                    <div class="comment-content" style="flex: 1;">
                        <div class="comment-author" style="font-weight: 600; color: #2d3748; font-size: 0.9rem;">
                            ${escapeHtml(comment.username)}
                        </div>
                        <div class="comment-text" style="color: #4a5568; font-size: 0.9rem; margin: 0.25rem 0;">
                            ${escapeHtml(comment.content)}
                        </div>
                        <div class="comment-time" style="color: #718096; font-size: 0.8rem;">
                            ${commentTime}
                        </div>
                    </div>
                </div>
            `;
      })
      .join("");
  }

  // コメント投稿処理
  window.submitComment = function (photoId) {
    const commentInput = document.querySelector(
      `#comments-${photoId} .comment-input`
    );
    const submitBtn = document.querySelector(
      `#comments-${photoId} .comment-submit`
    );

    if (!commentInput || !submitBtn) return;

    const content = commentInput.value.trim();
    if (!content) {
      alert("コメントを入力してください");
      return;
    }

    if (content.length > 500) {
      alert("コメントは500文字以下で入力してください");
      return;
    }

    // ボタンを無効化
    submitBtn.disabled = true;
    submitBtn.textContent = "投稿中...";

    const formData = new FormData();
    formData.append("photo_id", photoId);
    formData.append("content", content);

    fetch("api/comment.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // コメント入力欄をクリア
          commentInput.value = "";

          // コメント一覧を更新
          addCommentToList(photoId, data.comment);

          // コメント数を更新
          updateCommentCount(photoId, data.comments_count);

          // 成功メッセージ
          showMessage("コメントを投稿しました", "success");
        } else {
          alert(data.message || "コメントの投稿に失敗しました");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("エラーが発生しました");
      })
      .finally(() => {
        // ボタンを有効化
        submitBtn.disabled = false;
        submitBtn.textContent = "投稿";
      });
  };

  // コメントをリストに追加
  function addCommentToList(photoId, comment) {
    const commentsList = document.getElementById(`comments-list-${photoId}`);
    if (!commentsList) return;

    // 「コメントはありません」メッセージを削除
    const noCommentsMsg = commentsList.querySelector("p");
    if (
      noCommentsMsg &&
      noCommentsMsg.textContent.includes("コメントがありません")
    ) {
      noCommentsMsg.remove();
    }

    const avatarText = comment.username.charAt(0).toUpperCase();
    const commentTime = formatPostTime(comment.created_at);

    const commentElement = document.createElement("div");
    commentElement.className = "comment-item";
    commentElement.dataset.commentId = comment.id;
    commentElement.style.cssText =
      "display: flex; gap: 0.75rem; margin-bottom: 0.75rem;";

    commentElement.innerHTML = `
        <div class="comment-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem; flex-shrink: 0;">
            ${avatarText}
        </div>
        <div class="comment-content" style="flex: 1;">
            <div class="comment-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="comment-author" style="font-weight: 600; color: #2d3748; font-size: 0.9rem;">
                    ${escapeHtml(comment.username)}
                </div>
                <button class="comment-delete-btn" onclick="deleteComment(${
                  comment.id
                }, ${photoId})" 
                        style="background: none; border: none; color: #718096; cursor: pointer; font-size: 0.8rem; padding: 0.25rem;">
                    削除
                </button>
            </div>
            <div class="comment-text" style="color: #4a5568; font-size: 0.9rem; margin: 0.25rem 0;">
                ${escapeHtml(comment.content)}
            </div>
            <div class="comment-time" style="color: #718096; font-size: 0.8rem;">
                ${commentTime}
            </div>
        </div>
    `;

    commentsList.appendChild(commentElement);

    // スクロールして新しいコメントを表示
    commentElement.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  // コメント削除処理
  window.deleteComment = function (commentId, photoId) {
    if (!confirm("このコメントを削除しますか？")) {
      return;
    }

    const formData = new FormData();
    formData.append("comment_id", commentId);

    fetch("api/delete_comment.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // コメント要素を削除
          const commentElement = document.querySelector(
            `[data-comment-id="${commentId}"]`
          );
          if (commentElement) {
            commentElement.remove();
          }

          // コメント数を更新
          updateCommentCount(photoId, data.comments_count);

          // コメントがなくなった場合のメッセージ表示
          const commentsList = document.getElementById(
            `comments-list-${photoId}`
          );
          if (commentsList && commentsList.children.length === 0) {
            commentsList.innerHTML =
              '<p style="text-align: center; color: #718096; padding: 1rem;">まだコメントがありません</p>';
          }

          showMessage("コメントを削除しました", "success");
        } else {
          alert(data.message || "コメントの削除に失敗しました");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("エラーが発生しました");
      });
  };

  // コメント数を更新
  function updateCommentCount(photoId, newCount) {
    const commentCountElement = document.querySelector(
      `[data-photo-id="${photoId}"] .comment-count`
    );
    if (commentCountElement) {
      commentCountElement.textContent = newCount;
    }
  }

  // コメント表示の改善
  window.toggleComments = function (photoId) {
    const commentsSection = document.getElementById(`comments-${photoId}`);
    if (!commentsSection) return;

    if (commentsSection.style.display === "none") {
      commentsSection.style.display = "block";
      loadComments(photoId);

      // コメント入力欄にフォーカス
      setTimeout(() => {
        const commentInput = commentsSection.querySelector(".comment-input");
        if (commentInput) {
          commentInput.focus();
        }
      }, 100);
    } else {
      commentsSection.style.display = "none";
    }
  };

  // コメント表示関数の更新
  function displayComments(photoId, comments) {
    const commentsList = document.getElementById(`comments-list-${photoId}`);
    if (!commentsList) return;

    if (comments.length === 0) {
      commentsList.innerHTML =
        '<p style="text-align: center; color: #718096; padding: 1rem;">まだコメントがありません</p>';
      return;
    }

    commentsList.innerHTML = comments
      .map((comment) => {
        const avatarText = comment.username.charAt(0).toUpperCase();
        const commentTime = formatPostTime(comment.created_at);

        return `
            <div class="comment-item" data-comment-id="${
              comment.id
            }" style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div class="comment-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem; flex-shrink: 0;">
                    ${avatarText}
                </div>
                <div class="comment-content" style="flex: 1;">
                    <div class="comment-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="comment-author" style="font-weight: 600; color: #2d3748; font-size: 0.9rem;">
                            ${escapeHtml(comment.username)}
                        </div>
                        <button class="comment-delete-btn" onclick="deleteComment(${
                          comment.id
                        }, ${photoId})" 
                                style="background: none; border: none; color: #718096; cursor: pointer; font-size: 0.8rem; padding: 0.25rem;">
                            削除
                        </button>
                    </div>
                    <div class="comment-text" style="color: #4a5568; font-size: 0.9rem; margin: 0.25rem 0;">
                        ${escapeHtml(comment.content)}
                    </div>
                    <div class="comment-time" style="color: #718096; font-size: 0.8rem;">
                        ${commentTime}
                    </div>
                </div>
            </div>
        `;
      })
      .join("");
  }

  // メッセージ表示関数（既存のものを使用）
  function showMessage(message, type = "info") {
    if (typeof window.showMessage === "function") {
      window.showMessage(message, type);
    } else {
      alert(message);
    }
  }

  // いいねのトグル処理
  window.toggleLike = function (photoId, button) {
    if (!button) return;

    // ボタンの状態を取得
    const isLiked = button.classList.contains("liked");
    const action = isLiked ? "unlike" : "like";

    // ボタンを一時的に無効化
    button.disabled = true;

    const formData = new FormData();
    formData.append("photo_id", photoId);
    formData.append("action", action);

    fetch("api/like.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // ボタンの状態を更新
          updateLikeButton(button, data.liked, data.likes_count);

          // アニメーション効果
          if (data.liked) {
            animateLike(button);
          }
        } else {
          alert(data.message || "いいね処理に失敗しました");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("エラーが発生しました");
      })
      .finally(() => {
        // ボタンを有効化
        button.disabled = false;
      });
  };

  // いいねボタンの状態を更新
  function updateLikeButton(button, isLiked, likesCount) {
    const icon = button.querySelector("span:first-child");
    const countElement = button.querySelector(".like-count");

    if (isLiked) {
      button.classList.add("liked");
      icon.textContent = "❤️";
    } else {
      button.classList.remove("liked");
      icon.textContent = "🤍";
    }

    if (countElement) {
      countElement.textContent = likesCount;
    }
  }

  // いいねアニメーション
  function animateLike(button) {
    const icon = button.querySelector("span:first-child");
    if (!icon) return;

    // アニメーションクラスを追加
    icon.classList.add("like-animation");

    // アニメーション終了後にクラスを削除
    setTimeout(() => {
      icon.classList.remove("like-animation");
    }, 600);

    // ハートエフェクトを作成
    createHeartEffect(button);
  }

  // ハートエフェクト
  function createHeartEffect(button) {
    const heart = document.createElement("div");
    heart.textContent = "❤️";
    heart.className = "heart-effect";

    // ボタンの位置を取得
    const rect = button.getBoundingClientRect();
    heart.style.position = "fixed";
    heart.style.left = rect.left + rect.width / 2 + "px";
    heart.style.top = rect.top + "px";
    heart.style.fontSize = "1.5rem";
    heart.style.pointerEvents = "none";
    heart.style.zIndex = "1000";
    heart.style.animation = "heartFloat 1s ease-out forwards";

    document.body.appendChild(heart);

    // アニメーション終了後に要素を削除
    setTimeout(() => {
      if (heart.parentNode) {
        heart.parentNode.removeChild(heart);
      }
    }, 1000);
  }

  // いいね一覧を表示
  window.showLikes = function (photoId) {
    fetch(`api/get_likes.php?photo_id=${photoId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayLikesModal(data.likes, data.likes_count);
        } else {
          alert(data.message || "いいね一覧の取得に失敗しました");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("エラーが発生しました");
      });
  };

  // いいね一覧モーダルを表示
  function displayLikesModal(likes, likesCount) {
    const modal = document.createElement("div");
    modal.className = "likes-modal";
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    `;

    const modalContent = document.createElement("div");
    modalContent.style.cssText = `
        background: white;
        border-radius: 12px;
        padding: 2rem;
        max-width: 400px;
        max-height: 500px;
        overflow-y: auto;
        position: relative;
    `;

    modalContent.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0;">いいね（${likesCount}）</h3>
            <button onclick="this.closest('.likes-modal').remove()" 
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        <div class="likes-list">
            ${
              likes.length > 0
                ? likes
                    .map(
                      (like) => `
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                        ${like.username.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #2d3748;">${escapeHtml(
                          like.username
                        )}</div>
                        <div style="font-size: 0.875rem; color: #718096;">${formatPostTime(
                          like.created_at
                        )}</div>
                    </div>
                </div>
            `
                    )
                    .join("")
                : '<p style="text-align: center; color: #718096;">まだいいねがありません</p>'
            }
        </div>
    `;

    modal.appendChild(modalContent);
    document.body.appendChild(modal);

    // モーダル外クリックで閉じる
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.remove();
      }
    });
  }

  // 写真カード生成時にいいね数クリックイベントを追加
  function createPhotoCard(photo) {
    const card = document.createElement("div");
    card.className = "photo-card";
    card.dataset.photoId = photo.id;

    const avatarText = photo.username.charAt(0).toUpperCase();
    const postTime = formatPostTime(photo.created_at);

    card.innerHTML = `
        <div class="card-header">
            <div class="user-info">
                <div class="user-avatar">${avatarText}</div>
                <div class="user-details">
                    <h3>${escapeHtml(photo.username)}</h3>
                    <p class="post-time">${postTime}</p>
                </div>
            </div>
        </div>
        <div class="card-image" onclick="openPhotoModal(${photo.id})">
            <img src="${photo.image_url}" alt="${escapeHtml(
      photo.description || ""
    )}" loading="lazy" 
                 onerror="this.src='assets/images/no-image.png'">
        </div>
        ${
          photo.description
            ? `
            <div class="card-content">
                <p class="card-description">${escapeHtml(photo.description)}</p>
            </div>
        `
            : ""
        }
        <div class="card-actions">
            <button class="action-btn like-btn ${
              photo.user_liked ? "liked" : ""
            }" 
                    onclick="toggleLike(${photo.id}, this)">
                <span>${photo.user_liked ? "❤️" : "🤍"}</span>
                <span class="like-count" onclick="event.stopPropagation(); showLikes(${
                  photo.id
                })" 
                      style="cursor: pointer; text-decoration: underline;">${
                        photo.likes_count
                      }</span>
            </button>
            <button class="action-btn comment-btn" onclick="toggleComments(${
              photo.id
            })">
                <span>💬</span>
                <span class="comment-count">${photo.comments_count}</span>
            </button>
        </div>
        <div class="comments-section" id="comments-${
          photo.id
        }" style="display: none;">
            <div class="comments-list" id="comments-list-${photo.id}">
                <!-- コメントは動的に読み込み -->
            </div>
            <div class="comment-form">
                <input type="text" class="comment-input" placeholder="コメントを入力..." 
                       onkeypress="handleCommentKeyPress(event, ${photo.id})">
                <button class="comment-submit" onclick="submitComment(${
                  photo.id
                })">投稿</button>
            </div>
        </div>
    `;

    return card;
  }
});

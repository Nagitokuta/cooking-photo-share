// マイページ専用のJavaScript

document.addEventListener("DOMContentLoaded", function () {
  const myPosts = document.getElementById("myPosts");
  const emptyPosts = document.getElementById("emptyPosts");
  const gridViewBtn = document.getElementById("gridViewBtn");
  const listViewBtn = document.getElementById("listViewBtn");
  const editProfileBtn = document.getElementById("editProfileBtn");
  const editProfileModal = document.getElementById("editProfileModal");
  const closeEditModal = document.getElementById("closeEditModal");
  const cancelEditBtn = document.getElementById("cancelEditBtn");
  const editProfileForm = document.getElementById("editProfileForm");

  let currentView = "grid";
  let userPosts = [];

  // 初期読み込み
  loadUserStats();
  loadUserPosts();

  // 表示切り替えボタンのイベント
  gridViewBtn.addEventListener("click", () => switchView("grid"));
  listViewBtn.addEventListener("click", () => switchView("list"));

  // プロフィール編集モーダルのイベント
  editProfileBtn.addEventListener("click", openEditModal);
  closeEditModal.addEventListener("click", closeEditModalHandler);
  cancelEditBtn.addEventListener("click", closeEditModalHandler);
  editProfileForm.addEventListener("submit", handleProfileUpdate);

  // モーダル外クリックで閉じる
  editProfileModal.addEventListener("click", (e) => {
    if (e.target === editProfileModal) {
      closeEditModalHandler();
    }
  });

  // ユーザー統計情報を読み込み
  function loadUserStats() {
    fetch("api/get_user_stats.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          updateStats(data.stats);
        }
      })
      .catch((error) => {
        console.error("Error loading stats:", error);
      });
  }

  // 統計情報を更新
  function updateStats(stats) {
    document.getElementById("postsCount").textContent = stats.posts_count;
    document.getElementById("likesCount").textContent = stats.likes_count;
    document.getElementById("commentsCount").textContent = stats.comments_count;
  }

  // ユーザーの投稿一覧を読み込み
  function loadUserPosts() {
    fetch("api/get_user_posts.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          userPosts = data.posts;
          displayPosts();
        } else {
          showError(data.message || "投稿の読み込みに失敗しました");
        }
      })
      .catch((error) => {
        console.error("Error loading posts:", error);
        showError("エラーが発生しました");
      });
  }

  // 投稿を表示
  function displayPosts() {
    if (userPosts.length === 0) {
      myPosts.style.display = "none";
      emptyPosts.style.display = "block";
      return;
    }

    myPosts.style.display = currentView === "grid" ? "grid" : "flex";
    myPosts.className = currentView === "grid" ? "posts-grid" : "posts-list";
    emptyPosts.style.display = "none";

    myPosts.innerHTML = userPosts
      .map((post) => {
        if (currentView === "grid") {
          return createGridPostItem(post);
        } else {
          return createListPostItem(post);
        }
      })
      .join("");
  }

  // グリッド表示の投稿アイテムを作成
  function createGridPostItem(post) {
    return `
            <div class="post-item" onclick="openPostDetail(${post.id})">
                <img src="${
                  post.image_url
                }" alt="${escapeHtml(post.description || "")}" loading="lazy">
                <div class="post-overlay">
                    <div class="post-stats">
                        <span>❤️ ${post.likes_count}</span>
                        <span>💬 ${post.comments_count}</span>
                    </div>
                </div>
                <div class="post-actions">
                    <button class="post-action-btn delete" onclick="event.stopPropagation(); deletePost(${
                      post.id
                    })" title="削除">
                        🗑️
                    </button>
                </div>
            </div>
        `;
  }

  // リスト表示の投稿アイテムを作成
  function createListPostItem(post) {
    return `
            <div class="post-list-item">
                <div class="post-list-image" onclick="openPostDetail(${
                  post.id
                })">
                    <img src="${
                      post.image_url
                    }" alt="${escapeHtml(post.description || "")}" loading="lazy">
                </div>
                <div class="post-list-content">
                    <div class="post-list-description">
                        ${
                          post.description
                            ? escapeHtml(post.description)
                            : "説明なし"
                        }
                    </div>
                    <div class="post-list-meta">
                        <span>投稿日: ${formatDate(post.created_at)}</span>
                        <span>❤️ ${post.likes_count}</span>
                        <span>💬 ${post.comments_count}</span>
                    </div>
                </div>
                <div class="post-list-actions">
                    <button class="post-action-btn delete" onclick="deletePost(${
                      post.id
                    })" title="削除">
                        🗑️
                    </button>
                </div>
            </div>
        `;
  }

  // 表示切り替え
  function switchView(view) {
    currentView = view;

    gridViewBtn.classList.toggle("active", view === "grid");
    listViewBtn.classList.toggle("active", view === "list");

    displayPosts();
  }

  // プロフィール編集モーダルを開く
  function openEditModal() {
    // 現在の値を設定
    document.getElementById("editUsername").value =
      document.querySelector(".profile-username").textContent;
    document.getElementById("editEmail").value =
      document.querySelector(".profile-email").textContent;
    document.getElementById("editPassword").value = "";
    document.getElementById("editPasswordConfirm").value = "";

    editProfileModal.style.display = "block";
  }

  // プロフィール編集モーダルを閉じる
  function closeEditModalHandler() {
    editProfileModal.style.display = "none";
  }

  // プロフィール更新処理
  function handleProfileUpdate(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const password = formData.get("password");
    const passwordConfirm = formData.get("password_confirm");

    // パスワード確認
    if (password && password !== passwordConfirm) {
      alert("パスワードが一致しません");
      return;
    }

    fetch("api/update_profile.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // プロフィール情報を更新
          document.querySelector(".profile-username").textContent =
            data.user.username;
          document.querySelector(".profile-email").textContent =
            data.user.email;

          closeEditModalHandler();
          showMessage("プロフィールを更新しました", "success");
        } else {
          alert(data.message || "プロフィールの更新に失敗しました");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("エラーが発生しました");
      });
  }

  // 投稿削除
  window.deletePost = function (postId) {
    if (!confirm("この投稿を削除しますか？")) {
      return;
    }

    const formData = new FormData();
    formData.append("post_id", postId);

    fetch("api/delete_post.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // 投稿一覧から削除
          userPosts = userPosts.filter((post) => post.id !== postId);
          displayPosts();
          loadUserStats(); // 統計情報を更新
          showMessage("投稿を削除しました", "success");
        } else {
          alert(data.message || "投稿の削除に失敗しました");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("エラーが発生しました");
      });
  };

  // 投稿詳細を開く
  window.openPostDetail = function (postId) {
    // フィード画面の投稿詳細モーダルを開く
    window.location.href = `feed.php#post-${postId}`;
  };

  // エラー表示
  function showError(message) {
    myPosts.innerHTML = `
            <div class="error-container" style="text-align: center; padding: 3rem 1rem; grid-column: 1 / -1;">
                <p class="error-message" style="color: #e53e3e; margin-bottom: 1rem;">${message}</p>
                <button class="btn btn-secondary" onclick="location.reload()">再読み込み</button>
            </div>
        `;
  }

  // 日付フォーマット
  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("ja-JP");
  }

  // HTMLエスケープ
  function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // メッセージ表示
  function showMessage(message, type = "info") {
    if (typeof window.showMessage === "function") {
      window.showMessage(message, type);
    } else {
      alert(message);
    }
  }
});

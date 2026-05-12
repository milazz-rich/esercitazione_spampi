const commentsList = document.querySelector("#comments-list");
const commentForm = document.querySelector("#comment-form");
const statusBox = document.querySelector("#comment-status");
const sortSelect = document.querySelector("#comments-sort");

function escapeHtml(text) {
  return String(text)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function applyThemeFromStorage() {
  const savedTheme = localStorage.getItem("moviehub-theme");
  if (savedTheme === "dark") {
    document.body.classList.add("dark");
  }
}

function initThemeToggle() {
  const btn = document.querySelector("#theme-toggle");
  if (!btn) return;
  btn.addEventListener("click", () => {
    document.body.classList.toggle("dark");
    localStorage.setItem("moviehub-theme", document.body.classList.contains("dark") ? "dark" : "light");
  });
}

function renderComments(comments) {
  if (!commentsList) return;
  if (!comments.length) {
    commentsList.innerHTML = '<p class="subtitle">Non ci sono ancora commenti per questo film.</p>';
    return;
  }

  commentsList.innerHTML = comments
    .map((comment) => {
      const delBtn = comment.can_delete
        ? `<button type="button" class="delete-comment" data-comment-id="${Number(comment.id)}">Elimina</button>`
        : "";
      return `<article class="comment-item"><div class="comment-meta"><strong>${escapeHtml(comment.username)}</strong><span>${escapeHtml(comment.created_at)}</span></div><p>${escapeHtml(comment.text).replaceAll("\n", "<br>")}</p>${delBtn}</article>`;
    })
    .join("");
}

async function loadComments() {
  if (!commentsList) return;
  const movieId = commentsList.dataset.movieId;
  const sort = sortSelect ? sortSelect.value : "newest";
  const response = await fetch(`../api/api_comments.php?movie_id=${encodeURIComponent(movieId)}&sort=${encodeURIComponent(sort)}`);
  const data = await response.json();
  if (!response.ok || !Array.isArray(data)) throw new Error("Impossibile caricare i commenti");
  renderComments(data);
}

async function submitComment(event) {
  event.preventDefault();
  if (!commentForm) return;

  const formData = new FormData(commentForm);
  const response = await fetch("../api/api_add_comment.php", { method: "POST", body: formData });
  const data = await response.json();
  if (!response.ok || !data.ok) throw new Error(data.error || "Errore inserimento commento");

  commentForm.reset();
  await loadComments();
  if (statusBox) statusBox.textContent = "Commento pubblicato con successo.";
}

async function deleteComment(commentId) {
  const fd = new FormData();
  fd.append("comment_id", String(commentId));
  const response = await fetch("../api/api_delete_comment.php", { method: "POST", body: fd });
  const data = await response.json();
  if (!response.ok || !data.ok) throw new Error(data.error || "Errore eliminazione commento");
  await loadComments();
}

async function addLike(movieId, likesLabel) {
  const fd = new FormData();
  fd.append("movie_id", String(movieId));
  const response = await fetch("../api/api_like.php", { method: "POST", body: fd });
  const data = await response.json();
  if (!response.ok || !data.ok) throw new Error(data.error || "Errore aggiornamento like");
  if (likesLabel) likesLabel.textContent = `${Number(data.likes)} likes`;
}

applyThemeFromStorage();
initThemeToggle();

if (sortSelect) sortSelect.addEventListener("change", () => loadComments().catch((e) => statusBox && (statusBox.textContent = e.message)));

if (commentsList) {
  loadComments().catch((e) => statusBox && (statusBox.textContent = e.message));
  commentsList.addEventListener("click", (event) => {
    const btn = event.target.closest(".delete-comment");
    if (!btn) return;
    deleteComment(btn.dataset.commentId).catch((e) => statusBox && (statusBox.textContent = e.message));
  });
}

if (commentForm) {
  commentForm.addEventListener("submit", (event) => {
    submitComment(event).catch((e) => statusBox && (statusBox.textContent = e.message));
  });
}

document.querySelectorAll(".like-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const likesLabel = document.querySelector("[data-likes-label]");
    addLike(btn.dataset.movieId, likesLabel).catch((e) => statusBox && (statusBox.textContent = e.message));
  });
});

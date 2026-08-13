<?php
// editor.php - Blog Post Create & Edit Page
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Enforce authentication
requireAuth();

$postId   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit   = ($postId > 0);
$postData = ['title' => '', 'content' => ''];
$error    = null;

if ($isEdit) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, user_id, title, content FROM blogPost WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $postId]);
        $fetched = $stmt->fetch();

        if (!$fetched) {
            $error = "Blog post not found.";
        } else if (!isPostOwner($fetched['user_id'])) {
            $error = "Unauthorized: You can only edit your own blog posts.";
        } else {
            $postData = $fetched;
        }
    } catch (PDOException $e) {
        $error = "Database error while loading post.";
    }
}

$pageTitle = $isEdit ? "Edit Article" : "Write Article";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-narrow">
  <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo $isEdit ? 'post.php?id=' . $postId : 'index.php'; ?>" class="btn btn-secondary btn-sm">← Back to Articles</a>
    <span style="font-size: 0.88rem; font-weight: 700; color: var(--text-muted);">
      <?php echo $isEdit ? 'Editing Existing Article' : 'Drafting New Article'; ?>
    </span>
  </div>

  <?php if ($error): ?>
    <div class="auth-box text-center" style="max-width: 500px; margin: 40px auto;">
      <h2 class="auth-title">🚫 Access Restricted</h2>
      <p style="color: var(--text-muted); margin-bottom: 20px;"><?php echo sanitizeOutput($error); ?></p>
      <a href="index.php" class="btn btn-primary">Return to Home</a>
    </div>
  <?php else: ?>
    <div class="editor-workspace">
      <div id="editorAlertBox"></div>

      <form id="postEditorForm" data-mode="<?php echo $isEdit ? 'edit' : 'create'; ?>" data-post-id="<?php echo $postId; ?>">
        <div class="form-group">
          <input type="text" id="title" name="title" class="form-control editor-title-input" placeholder="Article Title..." value="<?php echo sanitizeOutput($postData['title']); ?>" required maxlength="255">
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <label class="form-label" style="margin-bottom: 0;">Article Content (Markdown Supported)</label>
            <div class="editor-tabs">
              <button type="button" class="editor-tab active" id="writeTabBtn">Write</button>
              <button type="button" class="editor-tab" id="previewTabBtn">Live Preview</button>
            </div>
          </div>

          <div id="writePane">
            <textarea id="content" name="content" class="form-control" style="min-height: 380px; font-family: var(--font-primary); font-size: 1.05rem; line-height: 1.7;" placeholder="Write your story here using Markdown...&#10;&#10;## Subheading&#10;Share your thoughts with generous spacing and clear formatting.&#10;&#10;* Highlight points with italics or **bold text**." required><?php echo htmlspecialchars($postData['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <div id="previewPane" class="hidden">
            <div id="previewContent" class="preview-box post-content-body"></div>
          </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 14px; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
          <a href="<?php echo $isEdit ? 'post.php?id=' . $postId : 'index.php'; ?>" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary btn-lg">
            <?php echo $isEdit ? 'Save Changes' : 'Publish Article'; ?>
          </button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>

<script src="assets/js/editor.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

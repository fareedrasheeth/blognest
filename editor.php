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
            // SERVER-SIDE OWNERSHIP CHECK FOR EDIT PAGE
            $error = "Unauthorized: You can only edit your own blog posts.";
        } else {
            $postData = $fetched;
        }
    } catch (PDOException $e) {
        $error = "Database error while loading post.";
    }
}

$pageTitle = $isEdit ? "Edit Post" : "Create New Post";
require_once __DIR__ . '/includes/header.php';
?>

<div class="editor-container">
  <div style="margin-bottom: 20px;">
    <a href="<?php echo $isEdit ? 'post.php?id=' . $postId : 'index.php'; ?>" class="btn btn-secondary btn-sm">← Cancel &amp; Return</a>
  </div>

  <?php if ($error): ?>
    <div class="auth-box text-center">
      <h2 style="margin-bottom: 12px;">🚫 Access Restricted</h2>
      <p class="text-muted" style="margin-bottom: 20px;"><?php echo sanitizeOutput($error); ?></p>
      <a href="index.php" class="btn btn-primary">Return to Home</a>
    </div>
  <?php else: ?>
    <div style="background: var(--bg-card); backdrop-filter: blur(12px); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-lg);">
      <h1 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
        <span><?php echo $isEdit ? '✏️ Edit Article' : '✍️ Create New Article'; ?></span>
      </h1>

      <div id="editorAlertBox"></div>

      <form id="postEditorForm" data-mode="<?php echo $isEdit ? 'edit' : 'create'; ?>" data-post-id="<?php echo $postId; ?>">
        <div class="form-group">
          <label for="title" class="form-label">Article Title</label>
          <input type="text" id="title" name="title" class="form-control" placeholder="Enter a descriptive title..." value="<?php echo sanitizeOutput($postData['title']); ?>" required maxlength="255">
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <label class="form-label" style="margin-bottom: 0;">Article Content (Markdown Supported)</label>
            <div class="editor-tabs">
              <button type="button" class="editor-tab active" id="writeTabBtn">Write</button>
              <button type="button" class="editor-tab" id="previewTabBtn">Live Preview</button>
            </div>
          </div>

          <div id="writePane">
            <textarea id="content" name="content" class="form-control" style="min-height: 320px; font-family: var(--font-code);" placeholder="Write your post here in Markdown format...&#10;&#10;# Heading 1&#10;**Bold text**, *Italics*, [Link](https://example.com)&#10;&#10;```javascript&#10;console.log('Hello BlogNest!');&#10;```" required><?php echo htmlspecialchars($postData['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <div id="previewPane" class="hidden">
            <div id="previewContent" class="preview-box post-content-body"></div>
          </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
          <a href="<?php echo $isEdit ? 'post.php?id=' . $postId : 'index.php'; ?>" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <?php echo $isEdit ? 'Save Changes' : 'Publish Article'; ?>
          </button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>

<script src="assets/js/editor.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

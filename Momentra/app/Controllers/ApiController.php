<?php

class ApiController extends Controller {

    // POST /api/like
    public function like(): void {
        $this->requireLogin();
        verifyCsrf();
        $me     = $this->currentUser();
        $postId = (int) ($_POST['post_id'] ?? 0);
        $model  = new LikeModel();
        $this->json($model->toggle($me['id'], $postId));
    }

    // POST /api/save
    public function save(): void {
        $this->requireLogin();
        verifyCsrf();
        $me     = $this->currentUser();
        $postId = (int) ($_POST['post_id'] ?? 0);
        $model  = new SaveModel();
        $this->json($model->toggle($me['id'], $postId));
    }

    // POST /api/comment
    public function comment(): void {
        $this->requireLogin();
        verifyCsrf();
        $me       = $this->currentUser();
        $postId   = (int) ($_POST['post_id'] ?? 0);
        $body     = trim($_POST['body'] ?? '');
        $parentId = (int) ($_POST['parent_id'] ?? 0);

        if (!$body) { $this->json(['error' => 'Empty comment'], 400); }

        $model   = new CommentModel();
        $comment = $model->add($me['id'], $postId, $body, $parentId);
        $this->json(['success' => true, 'comment' => $comment]);
    }

    // POST /api/delete-comment
    public function deleteComment(): void {
        $this->requireLogin();
        verifyCsrf();
        $me        = $this->currentUser();
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        $postOwnerId = (int) ($_POST['post_owner_id'] ?? 0);
        $model     = new CommentModel();
        $ok = $model->delete($commentId, $me['id'], $postOwnerId);
        $this->json(['success' => $ok]);
    }

    // POST /api/like-comment
    public function likeComment(): void {
        $this->requireLogin();
        verifyCsrf();
        $me        = $this->currentUser();
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        $model     = new CommentModel();
        $this->json($model->toggleLike($me['id'], $commentId));
    }

    // GET /api/replies
    public function getReplies(): void {
        $this->requireLogin();
        $me        = $this->currentUser();
        $commentId = (int) ($_GET['comment_id'] ?? 0);
        $model     = new CommentModel();
        $this->json($model->getReplies($commentId, $me['id']));
    }

    // POST /api/follow
    public function follow(): void {
        $this->requireLogin();
        verifyCsrf();
        $me          = $this->currentUser();
        $followingId = (int) ($_POST['following_id'] ?? 0);
        $model       = new FollowModel();
        $this->json($model->toggle($me['id'], $followingId));
    }

    // POST /api/delete-post
    public function deletePost(): void {
        $this->requireLogin();
        verifyCsrf();
        $me     = $this->currentUser();
        $postId = (int) ($_POST['post_id'] ?? 0);
        $model  = new PostModel();
        if ($model->delete($postId, $me['id'])) {
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Not found'], 404);
        }
    }

    // GET /api/likes
    public function getLikes(): void {
        $this->requireLogin();
        $postId = (int) ($_GET['post_id'] ?? 0);
        $model  = new LikeModel();
        $this->json($model->getLikers($postId));
    }

    // GET /api/search
    public function search(): void {
        $this->requireLogin();
        $q     = trim($_GET['q'] ?? '');
        $model = new UserModel();
        $this->json($model->search($q));
    }

    // GET /api/csrf
    public function csrfToken(): void {
        $this->json(['token' => csrfToken()]);
    }
}

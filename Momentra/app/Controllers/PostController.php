<?php

class PostController extends Controller {

    public function show(): void {
        $this->requireLogin();
        $me        = $this->currentUser();
        $postId    = (int) ($_GET['id'] ?? 0);
        $postModel = new PostModel();
        $commentModel = new CommentModel();

        $post = $postModel->getById($postId);
        if (!$post) { http_response_code(404); echo '404'; return; }

        $comments  = $commentModel->getWithReplies($postId, $me['id']);
        $pageTitle = 'Post';

        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('post.show', compact('post', 'comments', 'me'));
        $this->view('shared.footer');
    }

    public function create(): void {
        $this->requireLogin();
        $me = $this->currentUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $model  = new PostModel();
            $result = $model->create(
                $me['id'],
                $_POST['caption'] ?? '',
                $_POST['location'] ?? '',
                $_FILES['image'] ?? [],
                $_POST['post_type'] ?? 'photo',
                $_POST['text_bg'] ?? 'gradient-purple'
            );
            if ($result['success']) {
                $this->redirect(BASE_URL . '/');
            } else {
                $errors    = $result['errors'];
                $pageTitle = 'Create Post';
                $this->view('shared.header', compact('pageTitle', 'me'));
                $this->view('post.create', compact('errors', 'me'));
                $this->view('shared.footer');
                return;
            }
        }

        $pageTitle = 'Create Post';
        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('post.create', compact('me'));
        $this->view('shared.footer');
    }

    public function edit(): void {
        $this->requireLogin();
        $me        = $this->currentUser();
        $postId    = (int) ($_GET['id'] ?? 0);
        $postModel = new PostModel();
        $post      = $postModel->getById($postId);

        if (!$post || $post['user_id'] !== $me['id']) {
            $this->redirect(BASE_URL . '/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $postModel->update($postId, $me['id'], $_POST['caption'] ?? '', $_POST['location'] ?? '');
            $this->redirect(BASE_URL . '/post?id=' . $postId);
            return;
        }

        $pageTitle = 'Edit Post';
        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('post.edit', compact('post', 'me'));
        $this->view('shared.footer');
    }
}

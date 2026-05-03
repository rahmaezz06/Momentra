<?php

class ProfileController extends Controller {

    public function show(): void {
        $this->requireLogin();
        $me          = $this->currentUser();
        $username    = $_GET['u'] ?? '';
        $userModel   = new UserModel();
        $postModel   = new PostModel();
        $followModel = new FollowModel();

        $user = $username ? $userModel->getByUsername($username) : $me;
        if (!$user) { http_response_code(404); echo '404'; return; }

        $posts       = $postModel->getByUser($user['id']);
        $followers   = $followModel->getFollowerCount($user['id']);
        $following   = $followModel->getFollowingCount($user['id']);
        $isFollowing = $followModel->isFollowing($me['id'], $user['id']);
        $isMe        = $user['id'] === $me['id'];
        $pageTitle   = $user['username'];

        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('profile.show', compact('user', 'posts', 'followers', 'following', 'isFollowing', 'isMe', 'me'));
        $this->view('shared.footer');
    }

    public function edit(): void {
        $this->requireLogin();
        $me = $this->currentUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $userModel = new UserModel();
            $result    = $userModel->updateProfile($me['id'], $_POST, $_FILES['avatar'] ?? null);
            if ($result['success']) {
                $this->redirect(BASE_URL . '/profile?u=' . ($_POST['username'] ?? $me['username']));
                return;
            }
            $errors    = $result['errors'];
            $pageTitle = 'Edit Profile';
            $this->view('shared.header', compact('pageTitle', 'me'));
            $this->view('profile.edit', compact('me', 'errors'));
            $this->view('shared.footer');
            return;
        }

        $pageTitle = 'Edit Profile';
        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('profile.edit', compact('me'));
        $this->view('shared.footer');
    }

    public function followList(): void {
        $this->requireLogin();
        $me          = $this->currentUser();
        $username    = $_GET['u'] ?? '';
        $type        = $_GET['type'] ?? 'followers';
        $userModel   = new UserModel();
        $followModel = new FollowModel();

        $user = $userModel->getByUsername($username);
        if (!$user) { http_response_code(404); echo '404'; return; }

        $list      = $type === 'following'
            ? $followModel->getFollowing($user['id'], $me['id'])
            : $followModel->getFollowers($user['id'], $me['id']);
        $pageTitle = ucfirst($type);

        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('profile.follow_list', compact('user', 'list', 'type', 'me'));
        $this->view('shared.footer');
    }

    public function saved(): void {
        $this->requireLogin();
        $me        = $this->currentUser();
        $saveModel = new SaveModel();
        $posts     = $saveModel->getSaved($me['id']);
        $pageTitle = 'Saved Posts';

        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('profile.saved', compact('posts', 'me'));
        $this->view('shared.footer');
    }

    public function deleteAccount(): void {
        $this->requireLogin();
        verifyCsrf();
        $me       = $this->currentUser();
        $password = $_POST['password'] ?? '';

        $row = db_fetch_one('SELECT password_hash FROM users WHERE id = ?', 'i', [$me['id']]);
        if (!$row || !password_verify($password, $row['password_hash'])) {
            $this->json(['error' => 'Incorrect password.'], 403);
        }

        // Delete avatar file
        if ($me['avatar'] && $me['avatar'] !== 'default.png') {
            $avatarPath = UPLOAD_PATH . 'avatars/' . $me['avatar'];
            if (file_exists($avatarPath)) unlink($avatarPath);
        }

        db_query('DELETE FROM users WHERE id = ?', 'i', [$me['id']]);
        logout();
        $this->json(['success' => true, 'redirect' => BASE_URL . '/login']);
    }
}

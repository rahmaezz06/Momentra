<?php

class AuthController extends Controller {

    // ── Login ────────────────────────────────────────────────
    public function loginForm(): void {
        if (isLoggedIn()) $this->redirect(BASE_URL . '/');
        $pageTitle = 'Login';
        $this->view('shared.header', compact('pageTitle'));
        $this->view('auth.login');
        $this->view('shared.footer');
    }

    public function loginPost(): void {
        $email    = $_POST['email']    ?? '';
        $password = $_POST['password'] ?? '';

        if (login($email, $password)) {
            $this->redirect(BASE_URL . '/');
        } else {
            $error     = 'Invalid email or password.';
            $pageTitle = 'Login';
            $this->view('shared.header', compact('pageTitle'));
            $this->view('auth.login', compact('error'));
            $this->view('shared.footer');
        }
    }

    // ── Register ─────────────────────────────────────────────
    public function registerForm(): void {
        if (isLoggedIn()) $this->redirect(BASE_URL . '/');
        $pageTitle = 'Register';
        $this->view('shared.header', compact('pageTitle'));
        $this->view('auth.register');
        $this->view('shared.footer');
    }

    public function registerPost(): void {
        $result = register(
            $_POST['username']  ?? '',
            $_POST['email']     ?? '',
            $_POST['password']  ?? '',
            $_POST['full_name'] ?? ''
        );

        if ($result['success']) {
            login($_POST['email'], $_POST['password']);
            $this->redirect(BASE_URL . '/');
        } else {
            $errors    = $result['errors'];
            $pageTitle = 'Register';
            $this->view('shared.header', compact('pageTitle'));
            $this->view('auth.register', compact('errors'));
            $this->view('shared.footer');
        }
    }

    // ── Logout ───────────────────────────────────────────────
    public function logout(): void {
        logout();
        $this->redirect(BASE_URL . '/login');
    }

    // ── Forgot Password ──────────────────────────────────────
    public function forgotForm(): void {
        $pageTitle = 'Forgot Password';
        $this->view('shared.header', compact('pageTitle'));
        $this->view('auth.forgot_password');
        $this->view('shared.footer');
    }

    public function forgotPost(): void {
        $email   = strtolower(trim($_POST['email'] ?? ''));
        $message = '';
        $error   = '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $user = db_fetch_one('SELECT id, username FROM users WHERE email = ?', 's', [$email]);
            if ($user) {
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);
                db_query('DELETE FROM password_resets WHERE user_id = ?', 'i', [$user['id']]);
                db_query('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)', 'iss', [$user['id'], $token, $expires]);
                $resetLink = BASE_URL . '/reset-password?token=' . $token;
                $message   = 'reset_link:' . $resetLink;
            } else {
                $message = 'sent';
            }
        }

        $pageTitle = 'Forgot Password';
        $this->view('shared.header', compact('pageTitle'));
        $this->view('auth.forgot_password', compact('message', 'error'));
        $this->view('shared.footer');
    }

    // ── Reset Password ───────────────────────────────────────
    public function resetForm(): void {
        $token  = trim($_GET['token'] ?? '');
        $error  = '';
        $valid  = false;
        $userId = null;

        if ($token) {
            $row = db_fetch_one(
                'SELECT pr.*, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ?',
                's', [$token]
            );
            if (!$row)            $error = 'Invalid or expired link. Please request a new one.';
            elseif ($row['used']) $error = 'This link has already been used.';
            elseif (date('Y-m-d H:i:s') > $row['expires_at']) $error = 'This link has expired.';
            else { $valid = true; $userId = $row['user_id']; }
        } else {
            $error = 'No reset token provided.';
        }

        $pageTitle = 'Reset Password';
        $this->view('shared.header', compact('pageTitle'));
        $this->view('auth.reset_password', compact('token', 'error', 'valid', 'userId'));
        $this->view('shared.footer');
    }

    public function resetPost(): void {
        $token  = trim($_POST['token'] ?? '');
        $pw     = $_POST['password']   ?? '';
        $pw2    = $_POST['password2']  ?? '';
        $error  = '';
        $valid  = false;
        $userId = null;
        $success = false;

        $row = db_fetch_one('SELECT pr.* FROM password_resets pr WHERE pr.token = ?', 's', [$token]);

        if (!$row || $row['used'] || date('Y-m-d H:i:s') > $row['expires_at']) {
            $error = 'Invalid or expired link.';
        } else {
            $valid  = true;
            $userId = $row['user_id'];

            if (strlen($pw) < 6)  $error = 'Password must be at least 6 characters.';
            elseif ($pw !== $pw2) $error = 'Passwords do not match.';
            else {
                db_query('UPDATE users SET password_hash = ? WHERE id = ?', 'si', [password_hash($pw, PASSWORD_BCRYPT), $userId]);
                db_query('UPDATE password_resets SET used = 1 WHERE token = ?', 's', [$token]);
                $success = true;
            }
        }

        $pageTitle = 'Reset Password';
        $this->view('shared.header', compact('pageTitle'));
        $this->view('auth.reset_password', compact('token', 'error', 'valid', 'userId', 'success'));
        $this->view('shared.footer');
    }

    // ── Change Password ──────────────────────────────────────
    public function changeForm(): void {
        $this->requireLogin();
        $me        = $this->currentUser();
        $pageTitle = 'Change Password';
        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('auth.change_password');
        $this->view('shared.footer');
    }

    public function changePost(): void {
        $this->requireLogin();
        verifyCsrf();
        $me      = $this->currentUser();
        $errors  = [];
        $success = false;

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $row = db_fetch_one('SELECT password_hash FROM users WHERE id = ?', 'i', [$me['id']]);

        if (!password_verify($current, $row['password_hash']))
            $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 6)
            $errors[] = 'New password must be at least 6 characters.';
        if ($new === $current)
            $errors[] = 'New password must be different from your current password.';
        if ($new !== $confirm)
            $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            db_query('UPDATE users SET password_hash = ? WHERE id = ?', 'si', [password_hash($new, PASSWORD_BCRYPT), $me['id']]);
            $success = true;
        }

        $pageTitle = 'Change Password';
        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('auth.change_password', compact('errors', 'success'));
        $this->view('shared.footer');
    }
}

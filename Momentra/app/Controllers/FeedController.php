<?php

class FeedController extends Controller {

    public function index(): void {
        $this->requireLogin();
        $me    = $this->currentUser();
        $model = new PostModel();
        $posts = $model->getFeedPosts($me['id']);

        $pageTitle = 'Feed';
        $this->view('shared.header', compact('pageTitle', 'me'));
        $this->view('feed.index', compact('posts', 'me'));
        $this->view('shared.footer');
    }
}

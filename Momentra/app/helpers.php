<?php

function h($string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function publicUrl(): string {
    return rtrim(str_replace('/index.php', '', BASE_URL), '/');
}

function avatarUrl(?string $avatar): string {
    if (!$avatar || $avatar === 'default.png') {
        return publicUrl() . '/img/default-avatar.png';
    }
    return publicUrl() . '/uploads/avatars/' . $avatar;
}

function postImageUrl(string $image): string {
    return publicUrl() . '/uploads/posts/' . $image;
}

function timeAgo(string $timestamp): string {
    $diff    = time() - strtotime($timestamp);
    $minutes = round($diff / 60);
    $hours   = round($diff / 3600);
    $days    = round($diff / 86400);
    $weeks   = round($diff / 604800);
    $months  = round($diff / 2629440);
    $years   = round($diff / 31553280);

    if ($diff <= 60)      return 'Just Now';
    if ($minutes <= 60)   return $minutes == 1  ? '1 minute ago'  : "$minutes minutes ago";
    if ($hours <= 24)     return $hours == 1    ? '1 hour ago'    : "$hours hours ago";
    if ($days <= 7)       return $days == 1     ? 'yesterday'     : "$days days ago";
    if ($weeks <= 4.3)    return $weeks == 1    ? '1 week ago'    : "$weeks weeks ago";
    if ($months <= 12)    return $months == 1   ? '1 month ago'   : "$months months ago";
    return $years == 1 ? '1 year ago' : "$years years ago";
}

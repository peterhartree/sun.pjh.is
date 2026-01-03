/**
 * Blog Template - Tweet Embed
 * Loads and renders Twitter/X embeds using syndication API
 */

(function() {
    'use strict';

    const SYNDICATION_URL = 'https://cdn.syndication.twimg.com/tweet-result';

    /**
     * Format a date like Twitter does
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-GB', options);
    }

    /**
     * Format numbers with K/M suffix
     */
    function formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
        }
        return num.toString();
    }

    /**
     * Render tweet HTML
     */
    function renderTweet(tweet, username) {
        const user = tweet.user || {};
        const name = user.name || username;
        const screenName = user.screen_name || username;
        const profileImage = user.profile_image_url_https || '';
        const verified = user.is_blue_verified || user.verified;
        const text = tweet.text || '';
        const createdAt = tweet.created_at || '';
        const likes = tweet.favorite_count || 0;
        const retweets = tweet.retweet_count || 0;
        const tweetId = tweet.id_str || '';

        // Process text to add links
        let processedText = text;

        // Link hashtags
        processedText = processedText.replace(/#(\w+)/g, '<a href="https://twitter.com/hashtag/$1" target="_blank" rel="noopener">#$1</a>');

        // Link mentions
        processedText = processedText.replace(/@(\w+)/g, '<a href="https://twitter.com/$1" target="_blank" rel="noopener">@$1</a>');

        // Link URLs (basic)
        processedText = processedText.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');

        // Handle media
        let mediaHtml = '';
        if (tweet.mediaDetails && tweet.mediaDetails.length > 0) {
            const media = tweet.mediaDetails[0];
            if (media.type === 'photo') {
                mediaHtml = `<div class="tweet-media"><img src="${media.media_url_https}" alt="" loading="lazy"></div>`;
            } else if (media.type === 'video' || media.type === 'animated_gif') {
                const videoVariants = media.video_info?.variants || [];
                const mp4 = videoVariants.find(v => v.content_type === 'video/mp4');
                if (mp4) {
                    mediaHtml = `<div class="tweet-media"><video src="${mp4.url}" controls ${media.type === 'animated_gif' ? 'autoplay loop muted' : ''}></video></div>`;
                }
            }
        }

        const verifiedBadge = verified ? '<svg class="tweet-verified" viewBox="0 0 22 22" aria-label="Verified account"><path fill="currentColor" d="M20.396 11c-.018-.646-.215-1.275-.57-1.816-.354-.54-.852-.972-1.438-1.246.223-.607.27-1.264.14-1.897-.131-.634-.437-1.218-.882-1.687-.47-.445-1.053-.75-1.687-.882-.633-.13-1.29-.083-1.897.14-.273-.587-.704-1.086-1.245-1.44S11.647 1.62 11 1.604c-.646.017-1.273.213-1.813.568s-.969.854-1.24 1.44c-.608-.223-1.267-.272-1.902-.14-.635.13-1.22.436-1.69.882-.445.47-.749 1.055-.878 1.688-.13.633-.08 1.29.144 1.896-.587.274-1.087.705-1.443 1.245-.356.54-.555 1.17-.574 1.817.02.647.218 1.276.574 1.817.356.54.856.972 1.443 1.245-.224.606-.274 1.263-.144 1.896.13.634.433 1.218.877 1.688.47.443 1.054.747 1.687.878.633.132 1.29.084 1.897-.136.274.586.705 1.084 1.246 1.439.54.354 1.17.551 1.816.569.647-.016 1.276-.213 1.817-.567s.972-.854 1.245-1.44c.604.239 1.266.296 1.903.164.636-.132 1.22-.447 1.68-.907.46-.46.776-1.044.908-1.681s.075-1.299-.165-1.903c.586-.274 1.084-.705 1.439-1.246.354-.54.551-1.17.569-1.816zM9.662 14.85l-3.429-3.428 1.293-1.302 2.072 2.072 4.4-4.794 1.347 1.246z"></path></svg>' : '';

        return `
            <div class="tweet-card">
                <div class="tweet-header">
                    <a href="https://twitter.com/${screenName}" target="_blank" rel="noopener" class="tweet-author">
                        ${profileImage ? `<img src="${profileImage}" alt="" class="tweet-avatar">` : '<div class="tweet-avatar-placeholder"></div>'}
                        <div class="tweet-author-info">
                            <span class="tweet-author-name">${name}${verifiedBadge}</span>
                            <span class="tweet-author-handle">@${screenName}</span>
                        </div>
                    </a>
                    <a href="https://twitter.com/${screenName}/status/${tweetId}" target="_blank" rel="noopener" class="tweet-logo" aria-label="View on Twitter">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>
                    </a>
                </div>
                <div class="tweet-body">${processedText}</div>
                ${mediaHtml}
                <div class="tweet-footer">
                    <a href="https://twitter.com/${screenName}/status/${tweetId}" target="_blank" rel="noopener" class="tweet-date">${formatDate(createdAt)}</a>
                    <div class="tweet-stats">
                        <span class="tweet-stat" title="Retweets">
                            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M4.5 3.88l4.432 4.14-1.364 1.46L5.5 7.55V16c0 1.1.896 2 2 2H13v2H7.5c-2.209 0-4-1.79-4-4V7.55L1.432 9.48.068 8.02 4.5 3.88zM16.5 6H11V4h5.5c2.209 0 4 1.79 4 4v8.45l2.068-1.93 1.364 1.46-4.432 4.14-4.432-4.14 1.364-1.46 2.068 1.93V8c0-1.1-.896-2-2-2z"></path></svg>
                            ${formatNumber(retweets)}
                        </span>
                        <span class="tweet-stat" title="Likes">
                            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M16.697 5.5c-1.222-.06-2.679.51-3.89 2.16l-.805 1.09-.806-1.09C9.984 6.01 8.526 5.44 7.304 5.5c-1.243.07-2.349.78-2.91 1.91-.552 1.12-.633 2.78.479 4.82 1.074 1.97 3.257 4.27 7.129 6.61 3.87-2.34 6.052-4.64 7.126-6.61 1.111-2.04 1.03-3.7.477-4.82-.561-1.13-1.666-1.84-2.908-1.91zm4.187 7.69c-1.351 2.48-4.001 5.12-8.379 7.67l-.503.3-.504-.3c-4.379-2.55-7.029-5.19-8.382-7.67-1.36-2.5-1.41-4.86-.514-6.67.887-1.79 2.647-2.91 4.601-3.01 1.651-.09 3.368.56 4.798 2.01 1.429-1.45 3.146-2.1 4.796-2.01 1.954.1 3.714 1.22 4.601 3.01.896 1.81.846 4.17-.514 6.67z"></path></svg>
                            ${formatNumber(likes)}
                        </span>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render error state
     */
    function renderError(username, tweetId) {
        return `
            <div class="tweet-card tweet-error">
                <p>Tweet unavailable</p>
                <a href="https://twitter.com/${username}/status/${tweetId}" target="_blank" rel="noopener">View on Twitter</a>
            </div>
        `;
    }

    /**
     * Load a single tweet
     */
    async function loadTweet(container) {
        const tweetId = container.dataset.tweetId;
        const username = container.dataset.username;

        try {
            const response = await fetch(`${SYNDICATION_URL}?id=${tweetId}&token=0`);

            if (!response.ok) {
                throw new Error('Failed to load tweet');
            }

            const tweet = await response.json();
            container.innerHTML = renderTweet(tweet, username);
        } catch (error) {
            console.error('Error loading tweet:', error);
            container.innerHTML = renderError(username, tweetId);
        }
    }

    /**
     * Initialize all tweet embeds on the page
     */
    function init() {
        const tweetContainers = document.querySelectorAll('.tweet-embed');
        tweetContainers.forEach(loadTweet);
    }

    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

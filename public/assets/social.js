// public/assets/social.js
// Robust AJAX for likes/comments/share/delete — auto-detect project base
document.addEventListener('DOMContentLoaded', function () {
  // get base path from meta tag or from the script src if meta missing
  function detectBase() {
    const meta = document.querySelector('meta[name="base-path"]');
    if (meta && meta.content !== undefined) {
      console.info('[social.js] using base from meta:', meta.content);
      return meta.content.replace(/\/+$/, '');
    }
    // fallback: find this script tag's src
    const scripts = document.getElementsByTagName('script');
    for (let i = scripts.length - 1; i >= 0; i--) {
      const s = scripts[i];
      if (!s.src) continue;
      if (s.src.indexOf('social.js') !== -1) {
        try {
          const url = new URL(s.src, window.location.href);
          // remove everything after the public folder if present
          // return path up to /public if found
          const parts = url.pathname.split('/');
          // attempt to find "public" segment
          const pubIdx = parts.indexOf('public');
          if (pubIdx !== -1) {
            return parts.slice(0, pubIdx + 1).join('/');
          }
          // otherwise remove script path and return parent folder
          return parts.slice(0, parts.length - 2).join('/');
        } catch (e) {
          continue;
        }
      }
    }
    // last resort: use dirname of current location
    const loc = window.location.pathname;
    return loc.replace(/\/+$/, '');
  }

  const BASE = detectBase();
  console.info('[social.js] computed BASE =', BASE);

  function joinPath(path) {
    if (!BASE) return path;
    return (BASE + '/' + path).replace(/\/+/g, '/');
  }

  function esc(s) { return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

  function postForm(url, data) {
    const body = Object.keys(data).map(k => encodeURIComponent(k) + '=' + encodeURIComponent(data[k])).join('&');
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body
    }).then(res => {
      const ct = res.headers.get('content-type') || '';
      if (ct.indexOf('application/json') !== -1) return res.json();
      return res.text().then(text => ({ raw: text, status: res.status }));
    });
  }

  // LIKE
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-like');
    if (!btn) return;
    const postId = btn.dataset.id;
    postForm(joinPath('/posts/like'), { post_id: postId })
      .then(data => {
        if (data && data.ok && typeof data.likes !== 'undefined') {
          const postEl = document.getElementById('post-' + postId);
          if (!postEl) return;
          const lc = postEl.querySelector('.like-count');
          if (lc) lc.textContent = data.likes;
          btn.dataset.liked = data.liked ? '1' : '0';
          btn.textContent = data.liked ? '❤ Liked' : '♡ Like';
          return;
        }
        console.warn('[social.js] like: non-json response, reloading', data);
        window.location.reload();
      }).catch(err => { console.error('[social.js] like error', err); alert('Network error (like)'); });
  });

  // SHARE
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-share');
    if (!btn) return;
    const postId = btn.dataset.id;
    postForm(joinPath('/posts/share'), { post_id: postId })
      .then(data => {
        if (data && data.ok && typeof data.shares !== 'undefined') {
          const postEl = document.getElementById('post-' + postId);
          if (!postEl) return;
          const sc = postEl.querySelector('.share-count');
          if (sc) sc.textContent = data.shares;
          btn.textContent = '🔁 Shared';
          setTimeout(() => btn.textContent = '🔁 Share', 1200);
          return;
        }
        console.warn('[social.js] share: non-json response', data);
        window.location.reload();
      }).catch(() => alert('Network error (share)'));
  });

  // OPEN COMMENTS
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-open-comments');
    if (!btn) return;
    const postId = btn.dataset.id;
    const el = document.getElementById('comments-' + postId);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    const input = el ? el.querySelector('.comment-input') : null;
    if (input) input.focus();
  });

  // COMMENT SUBMIT
  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!form.matches('.comment-form')) return;
    e.preventDefault();
    const postId = form.dataset.id || form.querySelector('[name=post_id]').value;
    const content = (form.querySelector('[name=content]').value || '').trim();
    if (!content) return;
    postForm(joinPath('/posts/comment'), { post_id: postId, content: content })
      .then(data => {
        if (data && data.comments) {
          const container = document.getElementById('comments-' + postId);
          if (!container) return;
          const formNode = container.querySelector('.comment-form');
          const html = data.comments.map(c => '<div class="comment"><strong>' + esc(c.name || 'User') + '</strong> ' + esc(c.content || '') + '</div>').join('');
          container.innerHTML = html;
          if (formNode) container.appendChild(formNode);
          if (formNode) formNode.querySelector('[name=content]').value = '';
          const postEl = document.getElementById('post-' + postId);
          if (postEl) {
            const cc = postEl.querySelector('.comment-count');
            if (cc) cc.textContent = data.comments.length;
          }
          return;
        }
        console.warn('[social.js] comment: non-json response', data);
        window.location.reload();
      }).catch(() => alert('Network error (comment)'));
  });

  // DELETE
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return;
    const postId = btn.dataset.id;
    if (!confirm('Delete this post? This cannot be undone.')) return;
    postForm(joinPath('/posts/delete'), { post_id: postId })
      .then(data => {
        // remove DOM node regardless (optimistic)
        const postEl = document.getElementById('post-' + postId);
        if (postEl) postEl.remove();
        return;
      }).catch(() => {
        // fallback: classic form submit
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = joinPath('/posts/delete');
        const i = document.createElement('input'); i.type = 'hidden'; i.name = 'post_id'; i.value = postId;
        f.appendChild(i); document.body.appendChild(f); f.submit();
      });
  });

});
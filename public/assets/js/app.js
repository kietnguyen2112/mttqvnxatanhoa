const publicMenuToggle = document.querySelector('[data-menu-toggle]');
const publicNav = document.querySelector('[data-nav]');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Initialize ticker - display 3 items scrolling left to right
const tickerTrack = document.querySelector('[data-ticker-track]');
if (tickerTrack && !reduceMotion) {
  const tickerItems = tickerTrack.querySelectorAll('.ticker-item');
  if (tickerItems.length > 0) {
    // Clone ticker items for seamless infinite loop
    const itemsToClone = Array.from(tickerItems);
    itemsToClone.forEach(item => {
      const clone = item.cloneNode(true);
      tickerTrack.appendChild(clone);
    });
  }
  
  const tickerContainer = tickerTrack.closest('.ticker-container');
  if (tickerContainer) {
    // Pause on hover
    tickerContainer.addEventListener('mouseenter', () => {
      tickerTrack.style.animationPlayState = 'paused';
    });
    tickerContainer.addEventListener('mouseleave', () => {
      tickerTrack.style.animationPlayState = 'running';
    });
  }
}

publicMenuToggle?.addEventListener('click', () => {
  const open = publicNav?.classList.toggle('open') ?? false;
  publicMenuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  publicMenuToggle.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu');
});

publicNav?.querySelectorAll('a').forEach((link) => {
  link.addEventListener('click', () => {
    if (!window.matchMedia('(max-width: 700px)').matches) return;
    publicNav.classList.remove('open');
    publicMenuToggle?.setAttribute('aria-expanded', 'false');
    publicMenuToggle?.setAttribute('aria-label', 'Mở menu');
  });
});

document.addEventListener('click', (event) => {
  if (!publicNav?.classList.contains('open')) return;
  const target = event.target;
  if (!(target instanceof Node)) return;
  if (publicNav.contains(target) || publicMenuToggle?.contains(target)) return;
  publicNav.classList.remove('open');
  publicMenuToggle?.setAttribute('aria-expanded', 'false');
  publicMenuToggle?.setAttribute('aria-label', 'Mở menu');
});

document.addEventListener('keydown', (event) => {
  if (event.key !== 'Escape' || !publicNav?.classList.contains('open')) return;
  publicNav.classList.remove('open');
  publicMenuToggle?.setAttribute('aria-expanded', 'false');
  publicMenuToggle?.setAttribute('aria-label', 'Mở menu');
  publicMenuToggle?.focus();
});

window.addEventListener('resize', () => {
  if (!publicNav || !publicMenuToggle) return;
  if (!window.matchMedia('(min-width: 701px)').matches) return;
  publicNav.classList.remove('open');
  publicMenuToggle.setAttribute('aria-expanded', 'false');
  publicMenuToggle.setAttribute('aria-label', 'Mở menu');
}, { passive: true });

const backToTopButton = document.querySelector('[data-back-to-top]');

if (backToTopButton) {
  const backToTopThreshold = 300;
  let backToTopVisible = false;
  let backToTopRafId = 0;

  const applyBackToTopVisibility = () => {
    backToTopRafId = 0;
    const shouldShow = window.scrollY > backToTopThreshold;
    if (shouldShow === backToTopVisible) return;

    backToTopVisible = shouldShow;
    backToTopButton.classList.toggle('is-visible', shouldShow);
    backToTopButton.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
    backToTopButton.tabIndex = shouldShow ? 0 : -1;
  };

  const queueBackToTopVisibility = () => {
    if (backToTopRafId !== 0) return;
    backToTopRafId = window.requestAnimationFrame(applyBackToTopVisibility);
  };

  backToTopButton.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
  });

  window.addEventListener('scroll', queueBackToTopVisibility, { passive: true });
  window.addEventListener('resize', queueBackToTopVisibility, { passive: true });
  queueBackToTopVisibility();
}

const adminShell = document.querySelector('[data-admin-shell]');
const adminSidebarToggle = document.querySelector('[data-admin-sidebar-toggle]');

if (adminShell && adminSidebarToggle) {
  const storageKey = 'mttq_admin_sidebar_collapsed';
  const applySidebarState = (collapsed) => {
    adminShell.classList.toggle('is-sidebar-collapsed', collapsed);
    document.documentElement.classList.toggle('admin-sidebar-collapsed-init', collapsed);
    adminSidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    adminSidebarToggle.setAttribute('aria-label', collapsed ? 'Mở rộng menu' : 'Thu gọn menu');
  };

  applySidebarState(localStorage.getItem(storageKey) === '1');

  adminSidebarToggle.addEventListener('click', () => {
    const collapsed = !adminShell.classList.contains('is-sidebar-collapsed');
    localStorage.setItem(storageKey, collapsed ? '1' : '0');
    applySidebarState(collapsed);
  });
}

document.querySelectorAll('[data-slug-source]').forEach((source) => {
  const form = source.closest('form');
  const target = form?.querySelector('[data-slug-target]');
  if (!(source instanceof HTMLInputElement) || !(target instanceof HTMLInputElement)) return;

  let slugEdited = target.value.trim() !== '';
  const slugify = (value) => value
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 180);

  target.addEventListener('input', () => {
    slugEdited = target.value.trim() !== '';
    target.value = slugify(target.value);
  });

  source.addEventListener('input', () => {
    if (slugEdited) return;
    target.value = slugify(source.value);
  });
});

const adminMenu = document.querySelector('.admin-menu');
const activeAdminMenuItem = adminMenu?.querySelector('a.active');

if (adminMenu && activeAdminMenuItem && window.matchMedia('(max-width: 900px)').matches) {
  requestAnimationFrame(() => {
    activeAdminMenuItem.scrollIntoView({
      block: 'nearest',
      inline: 'center',
      behavior: 'auto',
    });
  });
}

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
  button.addEventListener('click', () => {
    const field = button.closest('.password-field');
    const input = field?.querySelector('[data-password-input]');

    if (!input) return;

    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    button.classList.toggle('is-visible', isHidden);
    button.setAttribute('aria-label', isHidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
  });
});

document.querySelectorAll('[data-admin-auto-filter]').forEach((field) => {
  field.addEventListener('change', () => {
    const form = field.closest('form');
    if (!form) return;

    const hasTextFilter = Boolean(form.querySelector('input[name="q"]')?.value.trim());
    if (field instanceof HTMLSelectElement && field.value === '' && field.dataset.clearUrl && !hasTextFilter) {
      window.location.href = field.dataset.clearUrl;
      return;
    }

    form.submit();
  });
});

document.querySelectorAll('[data-leaders-filter]').forEach((form) => {
  const panel = form.closest('.leaders-list-panel');
  const searchInput = form.querySelector('[data-leaders-filter-search]');
  const orgSelect = form.querySelector('[data-leaders-filter-org]');
  const roleSelect = form.querySelector('[data-leaders-filter-role]');
  const countOutput = form.querySelector('[data-leaders-filter-count]');
  const emptyState = panel?.querySelector('[data-leaders-filter-empty]');
  const records = Array.from(panel?.querySelectorAll('[data-leader-record]') ?? []);
  const groups = Array.from(panel?.querySelectorAll('[data-leader-group]') ?? []);
  const orgPills = Array.from(panel?.querySelectorAll('[data-leader-org-pill]') ?? []);

  if (!panel || !searchInput || !orgSelect || !roleSelect || records.length === 0) return;

  const normalize = (value) => String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .trim();

  const applyLeaderFilters = () => {
    const query = normalize(searchInput.value);
    const org = orgSelect.value;
    const role = roleSelect.value;
    let visibleTotal = 0;

    records.forEach((record) => {
      const matchesQuery = !query || normalize(record.dataset.leaderSearch).includes(query);
      const matchesOrg = !org || record.dataset.leaderOrg === org;
      const matchesRole = !role || record.dataset.leaderRole === role;
      const visible = matchesQuery && matchesOrg && matchesRole;
      record.hidden = !visible;
      record.classList.toggle('is-filter-hidden', !visible);
      if (visible) visibleTotal += 1;
    });

    groups.forEach((group) => {
      const visibleCount = group.querySelectorAll('[data-leader-record]:not([hidden])').length;
      const countNode = group.querySelector('[data-leader-group-count]');
      if (countNode) countNode.textContent = `${visibleCount} hồ sơ`;
      group.hidden = visibleCount === 0;
      group.classList.toggle('is-filter-hidden', visibleCount === 0);
    });

    orgPills.forEach((pill) => {
      const visible = !org || pill.dataset.leaderOrgPill === org;
      pill.hidden = !visible;
      pill.classList.toggle('is-filter-hidden', !visible);
    });

    if (countOutput) countOutput.value = `${visibleTotal} hồ sơ`;
    if (emptyState) emptyState.hidden = visibleTotal !== 0;
    form.classList.toggle('is-filtering', Boolean(query || org || role));
  };

  form.addEventListener('submit', (event) => event.preventDefault());
  form.addEventListener('reset', () => {
    window.requestAnimationFrame(applyLeaderFilters);
  });
  searchInput.addEventListener('input', applyLeaderFilters);
  orgSelect.addEventListener('change', applyLeaderFilters);
  roleSelect.addEventListener('change', applyLeaderFilters);
  applyLeaderFilters();
});

document.querySelectorAll('[data-home-posts]').forEach((homePosts) => {
  const featured = homePosts.querySelector('[data-home-featured]');
  const mediaLink = homePosts.querySelector('[data-home-featured-media]');
  const titleLink = homePosts.querySelector('[data-home-featured-title]');
  const detailLink = homePosts.querySelector('[data-home-featured-link]');
  const dateNode = homePosts.querySelector('[data-home-featured-date]');
  const excerptNode = homePosts.querySelector('[data-home-featured-excerpt]');
  const secondaryPosts = homePosts.querySelectorAll('[data-home-secondary-post]');

  if (!featured || !mediaLink || !titleLink || !detailLink || !dateNode || !excerptNode) return;

  const defaultPost = JSON.parse(featured.dataset.defaultPost || '{}');

  const renderFeaturedPost = (post) => {
    if (!post || !post.url) return;

    mediaLink.href = post.url;
    titleLink.href = post.url;
    detailLink.href = post.url;
    titleLink.textContent = post.title || '';
    dateNode.textContent = post.date || '';
    excerptNode.textContent = post.excerpt || '';

    mediaLink.replaceChildren();
    if (post.image) {
      const image = document.createElement('img');
      image.src = `/${post.image.replace(/^\/+/, '')}`;
      image.alt = post.title || '';
      image.width = 640;
      image.height = 360;
      image.loading = 'lazy';
      image.decoding = 'async';
      image.dataset.homeFeaturedImage = '';
      mediaLink.append(image);
    } else {
      const placeholder = document.createElement('span');
      placeholder.textContent = 'Tin chính';
      placeholder.dataset.homeFeaturedPlaceholder = '';
      mediaLink.append(placeholder);
    }
  };

  const activateSecondaryPost = (card) => {
    const post = JSON.parse(card.dataset.post || '{}');
    renderFeaturedPost(post);
    secondaryPosts.forEach((item) => item.classList.toggle('active-preview', item === card));
  };

  secondaryPosts.forEach((card) => {
    card.addEventListener('mouseenter', () => activateSecondaryPost(card));
    card.addEventListener('focusin', () => activateSecondaryPost(card));
  });

  homePosts.addEventListener('mouseleave', () => {
    renderFeaturedPost(defaultPost);
    secondaryPosts.forEach((item) => {
      const post = JSON.parse(item.dataset.post || '{}');
      item.classList.toggle('active-preview', Number(post.id) === Number(defaultPost.id));
    });
  });
});

const getAdminPanelShell = (panel) => panel?.closest('.admin-editor-panel, .admin-content-card, .import-panel') ?? null;
const getAdminPanelPage = (panel) => panel?.closest('.admin-crud-page, .import-workspace') ?? null;

const ensureAdminPanelBackdrop = (panel) => {
  if (!panel?.id) return null;
  const parent = panel.parentElement;
  if (!parent) return null;

  let backdrop = parent.querySelector(`.admin-editor-modal-backdrop[data-admin-collapse-close="${panel.id}"]`);
  if (backdrop) return backdrop;

  backdrop = document.createElement('button');
  backdrop.type = 'button';
  backdrop.className = 'admin-editor-modal-backdrop';
  backdrop.dataset.adminCollapseClose = panel.id;
  backdrop.setAttribute('aria-label', 'Đóng cửa sổ nhập nội dung');
  parent.insertBefore(backdrop, panel);
  return backdrop;
};

const openAdminPanel = (panel, trigger = null) => {
  if (!panel) return;
  const modalShell = getAdminPanelShell(panel);
  const editorPage = getAdminPanelPage(panel);
  ensureAdminPanelBackdrop(panel);
  panel.classList.add('is-open');
  modalShell?.classList.add('is-modal-open');
  editorPage?.classList.add('is-editor-page');
  document.body.classList.add('admin-editor-page-open');
  panel.removeAttribute('role');
  panel.removeAttribute('aria-modal');
  if (trigger) {
    trigger.setAttribute('aria-expanded', 'true');
  }

  window.requestAnimationFrame(() => {
    editorPage?.scrollIntoView({ block: 'start', behavior: reduceMotion ? 'auto' : 'smooth' });
    panel.querySelector('[data-tinymce-editor]')?.dispatchEvent(new Event('admin-panel-opened'));
    panel.querySelector('input, select, textarea, button')?.focus({ preventScroll: true });
  });
};

const closeAdminPanel = (panel, trigger = null) => {
  if (!panel) return;
  const modalShell = getAdminPanelShell(panel);
  const editorPage = getAdminPanelPage(panel);
  panel.classList.remove('is-open');
  modalShell?.classList.remove('is-modal-open');
  editorPage?.classList.remove('is-editor-page');
  if (!document.querySelector('[data-admin-collapse-panel].is-open')) {
    document.body.classList.remove('admin-modal-open');
    document.body.classList.remove('admin-editor-page-open');
  }
  if (trigger) {
    trigger.setAttribute('aria-expanded', 'false');
  }
};

const ensureAdminPageActions = () => {
  const sectionHead = document.querySelector('.admin-list-panel .section-head, .leaders-list-panel .section-head');
  if (!sectionHead) return null;

  let actions = sectionHead.querySelector('.admin-page-actions');
  if (!actions) {
    actions = document.createElement('div');
    actions.className = 'admin-page-actions';
    sectionHead.appendChild(actions);
  }

  return actions;
};

const moveAdminHeaderActionsToPage = () => {
  const pageActions = ensureAdminPageActions();
  const trigger = document.querySelector('.admin-content-card [data-admin-collapse-toggle]');
  const topbarTools = document.querySelector('.admin-topbar-tools');

  if (pageActions && trigger) {
    trigger.classList.add('admin-topbar-content-action', 'admin-page-action-button');
    trigger.dataset.adminHeaderIcon = trigger.classList.contains('is-editing') ? 'edit' : 'add';
    trigger.setAttribute('title', trigger.classList.contains('is-editing') ? 'Sửa nội dung' : 'Thêm nội dung');
    trigger.setAttribute('aria-label', trigger.classList.contains('is-editing') ? 'Sửa nội dung' : 'Thêm nội dung');
    if (!trigger.querySelector('.bi')) {
      const icon = document.createElement('i');
      icon.className = trigger.classList.contains('is-editing') ? 'bi bi-pencil-square' : 'bi bi-plus-circle-fill';
      icon.setAttribute('aria-hidden', 'true');
      const text = document.createElement('span');
      text.className = 'admin-action-text';
      text.textContent = trigger.textContent.trim();
      trigger.replaceChildren(icon, text);
    }
    pageActions.appendChild(trigger);
  }

  if (pageActions && topbarTools) {
    topbarTools.classList.add('admin-page-action-tools');
    pageActions.appendChild(topbarTools);
  }
};

moveAdminHeaderActionsToPage();

document.querySelectorAll('[data-admin-collapse-panel]').forEach((panel) => {
  ensureAdminPanelBackdrop(panel);
});

document.querySelectorAll('[data-admin-collapse-toggle]').forEach((trigger) => {
  const panelId = trigger.dataset.adminCollapseToggle;
  const panel = panelId ? document.getElementById(panelId) : null;
  if (!panel) return;

  trigger.addEventListener('click', () => {
    if (panel.classList.contains('is-open')) {
      closeAdminPanel(panel, trigger);
    } else {
      openAdminPanel(panel, trigger);
    }
  });
});

document.querySelectorAll('[data-admin-collapse-close]').forEach((closeButton) => {
  const panelId = closeButton.dataset.adminCollapseClose;
  const panel = panelId ? document.getElementById(panelId) : closeButton.closest('[data-admin-collapse-panel]');
  const trigger = panel?.id ? document.querySelector(`[data-admin-collapse-toggle="${panel.id}"]`) : null;

  closeButton.addEventListener('click', () => closeAdminPanel(panel, trigger));
});

document.querySelectorAll('[data-admin-collapse-panel].is-open').forEach((panel) => {
  const trigger = panel.id ? document.querySelector(`[data-admin-collapse-toggle="${panel.id}"]`) : null;
  ensureAdminPanelBackdrop(panel);
  getAdminPanelShell(panel)?.classList.add('is-modal-open');
  getAdminPanelPage(panel)?.classList.add('is-editor-page');
  document.body.classList.add('admin-editor-page-open');
  panel.removeAttribute('role');
  panel.removeAttribute('aria-modal');
  trigger?.setAttribute('aria-expanded', 'true');
});

document.addEventListener('keydown', (event) => {
  if (event.key !== 'Escape') return;
  document.querySelectorAll('[data-admin-collapse-panel].is-open').forEach((panel) => {
    const trigger = panel.id ? document.querySelector(`[data-admin-collapse-toggle="${panel.id}"]`) : null;
    closeAdminPanel(panel, trigger);
  });
});

document.querySelectorAll('[data-tinymce-editor]').forEach((textarea) => {
  if (!window.tinymce) return;

  window.tinymce.init({
    selector: `#${textarea.id}`,
    height: 460,
    menubar: false,
    branding: false,
    promotion: false,
    language: 'vi',
    language_url: 'https://cdn.jsdelivr.net/npm/tinymce@6/langs/vi.js',
    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
    toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link image table | removeformat code fullscreen',
    block_formats: 'Đoạn văn=p; Tiêu đề 2=h2; Tiêu đề 3=h3; Tiêu đề 4=h4; Trích dẫn=blockquote',
    automatic_uploads: true,
    images_upload_url: textarea.dataset.uploadUrl || '/admin/posts/content-image',
    images_file_types: 'jpg,jpeg,png,webp',
    paste_data_images: true,
    relative_urls: false,
    remove_script_host: true,
    convert_urls: false,
    content_style: 'body{font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.65;color:#1f2937} img{max-width:100%;height:auto} figure{margin:12px 0} table{border-collapse:collapse;width:100%} td,th{border:1px solid #d0d5dd;padding:6px}',
    images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
      const blob = blobInfo.blob();
      if (!['image/jpeg', 'image/png', 'image/webp'].includes(blob.type)) {
        reject('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
        return;
      }
      if (blob.size > 2 * 1024 * 1024) {
        reject('Ảnh trong nội dung không được vượt quá 2 MB.');
        return;
      }

      const formData = new FormData();
      formData.append('_token', textarea.dataset.csrfToken || '');
      formData.append('file', blob, blobInfo.filename());

      const request = new XMLHttpRequest();
      request.open('POST', textarea.dataset.uploadUrl || '/admin/posts/content-image');
      request.upload.onprogress = (event) => {
        if (event.lengthComputable) {
          progress((event.loaded / event.total) * 100);
        }
      };
      request.onload = () => {
        let json = null;
        try {
          json = JSON.parse(request.responseText || '{}');
        } catch (error) {
          reject('Máy chủ trả về dữ liệu upload không hợp lệ.');
          return;
        }

        if (request.status < 200 || request.status >= 300 || typeof json.location !== 'string') {
          reject(json.error || 'Không thể tải ảnh lên.');
          return;
        }

        resolve(json.location);
      };
      request.onerror = () => reject('Không thể kết nối máy chủ upload ảnh.');
      request.send(formData);
    }),
    setup: (editor) => {
      const sync = () => editor.save();
      editor.on('change keyup undo redo', sync);
      textarea.closest('form')?.addEventListener('submit', sync);
      textarea.addEventListener('admin-panel-opened', () => {
        window.setTimeout(() => {
          editor.theme?.resizeTo(null, 460);
        }, 80);
      });
    },
  });
});

if (!reduceMotion) {
  document.documentElement.classList.add('ui-motion-ready');
  const revealItems = document.querySelectorAll(
    '.public-page > *, .loan-summary-card, .organizations-directory-card'
  );
  const observer = new IntersectionObserver((entries, revealObserver) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('ui-visible');
      revealObserver.unobserve(entry.target);
    });
  }, { rootMargin: '0px 0px -36px', threshold: 0.06 });

  revealItems.forEach((item, index) => {
    item.classList.add('ui-reveal');
    item.style.setProperty('--ui-delay', `${Math.min(index % 5, 4) * 32}ms`);
    observer.observe(item);
  });
}

document.querySelectorAll('form[data-ui-busy], .admin-form:not([onsubmit])').forEach((form) => {
  form.addEventListener('submit', (event) => {
    window.requestAnimationFrame(() => {
      if (event.defaultPrevented || !form.checkValidity()) return;
      form.classList.add('ui-submitting');
      form.setAttribute('aria-busy', 'true');
    });
  });
});

const loanBrowser = document.querySelector('[data-loan-browser]');

if (loanBrowser) {
  const loanResult = loanBrowser.querySelector('[data-loan-result]');
  const loanFilterLinks = Array.from(loanBrowser.querySelectorAll('[data-loan-filter-link]'));

  if (loanResult && loanFilterLinks.length) {
    const initializeLoanPagination = (resultPanel) => {
      const table = resultPanel.querySelector('[data-loan-table]');
      const tableWrap = resultPanel.querySelector('[data-loan-table-wrap]');
      const mobileCards = Array.from(resultPanel.querySelectorAll('[data-loan-mobile-card]'));
      const pagination = resultPanel.querySelector('[data-loan-pagination]');
      const status = pagination?.querySelector('[data-loan-page-status]');
      const controls = pagination?.querySelector('[data-loan-page-controls]');
      const previousButton = pagination?.querySelector('[data-loan-page-prev]');
      const nextButton = pagination?.querySelector('[data-loan-page-next]');
      const pageNumbers = pagination?.querySelector('[data-loan-page-numbers]');

      if (!table || !pagination || !status || !controls || !previousButton || !nextButton || !pageNumbers) {
        return;
      }

      const rows = Array.from(table.querySelectorAll('tbody > tr'));
      const pageSize = Math.max(1, Number.parseInt(pagination.dataset.pageSize ?? '8', 10));
      const totalPages = Math.ceil(rows.length / pageSize);
      const compactPagerQuery = window.matchMedia('(max-width: 700px)');
      let currentPage = 1;

      const getPageItems = () => {
        if (totalPages <= 1) {
          return [];
        }

        if (!compactPagerQuery.matches) {
          return Array.from({ length: totalPages }, (_, index) => index + 1);
        }

        const siblingCount = compactPagerQuery.matches ? 0 : 1;
        const pages = new Set([1, totalPages, currentPage]);

        for (let offset = 1; offset <= siblingCount; offset++) {
          if (currentPage - offset > 1) pages.add(currentPage - offset);
          if (currentPage + offset < totalPages) pages.add(currentPage + offset);
        }

        return Array.from(pages)
          .filter((page) => page >= 1 && page <= totalPages)
          .sort((a, b) => a - b)
          .reduce((items, page, index, sortedPages) => {
            const previousPage = sortedPages[index - 1];
            if (index > 0 && page - previousPage > 1) {
              items.push('gap');
            }
            items.push(page);
            return items;
          }, []);
      };

      const renderPage = (requestedPage) => {
        currentPage = Math.min(Math.max(1, requestedPage), Math.max(1, totalPages));
        const firstRow = (currentPage - 1) * pageSize;
        const lastRow = Math.min(firstRow + pageSize, rows.length);

        rows.forEach((row, index) => {
          row.hidden = index < firstRow || index >= lastRow;
        });
        mobileCards.forEach((card, index) => {
          card.hidden = index < firstRow || index >= lastRow;
        });

        status.textContent = totalPages > 1
          ? `Hiển thị ${firstRow + 1}-${lastRow} trong ${rows.length} tổ vay vốn`
          : `Hiển thị đầy đủ ${rows.length} tổ vay vốn`;
        tableWrap?.classList.toggle('is-paginated', totalPages > 1);
        controls.hidden = totalPages <= 1;
        previousButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;

        pageNumbers.replaceChildren();
        getPageItems().forEach((page) => {
          if (page === 'gap') {
            const gap = document.createElement('span');
            gap.className = 'loan-page-gap';
            gap.textContent = '...';
            gap.setAttribute('aria-hidden', 'true');
            pageNumbers.append(gap);
            return;
          }

          const pageButton = document.createElement('button');
          pageButton.type = 'button';
          pageButton.className = 'loan-page-number';
          pageButton.textContent = `${page}`;
          pageButton.setAttribute('aria-label', `Trang ${page}`);
          if (page === currentPage) {
            pageButton.classList.add('active');
            pageButton.setAttribute('aria-current', 'page');
          }
          pageButton.addEventListener('click', () => renderPage(page));
          pageNumbers.append(pageButton);
        });
      };

      previousButton.addEventListener('click', () => renderPage(currentPage - 1));
      nextButton.addEventListener('click', () => renderPage(currentPage + 1));
      if (typeof compactPagerQuery.addEventListener === 'function') {
        compactPagerQuery.addEventListener('change', () => renderPage(currentPage));
      } else if (typeof compactPagerQuery.addListener === 'function') {
        compactPagerQuery.addListener(() => renderPage(currentPage));
      }
      renderPage(1);
    };

    const normalizeLoanUrl = (address) => new URL(address, window.location.href).href;
    const resultCache = new Map();
    const pendingLoads = new Map();
    let selectionSequence = 0;
    let resultHeightTimer;

    const initialUrl = normalizeLoanUrl(window.location.href);
    resultCache.set(initialUrl, loanResult.innerHTML);

    const initialSelection = loanFilterLinks.find((link) => link.classList.contains('active'));
    if (initialSelection) {
      resultCache.set(normalizeLoanUrl(initialSelection.href), loanResult.innerHTML);
    }
    initializeLoanPagination(loanResult);

    const extractResultMarkup = (html) => {
      const responseDocument = new DOMParser().parseFromString(html, 'text/html');
      const responsePanel = responseDocument.querySelector('[data-loan-result]');
      return responsePanel?.innerHTML ?? null;
    };

    const loadResultMarkup = (address) => {
      const targetUrl = normalizeLoanUrl(address);
      if (resultCache.has(targetUrl)) {
        return Promise.resolve(resultCache.get(targetUrl));
      }
      if (pendingLoads.has(targetUrl)) {
        return pendingLoads.get(targetUrl);
      }

      const request = fetch(targetUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
        .then((response) => {
          if (!response.ok) throw new Error(`HTTP ${response.status}`);
          return response.text();
        })
        .then((html) => {
          const resultMarkup = extractResultMarkup(html);
          if (resultMarkup === null) throw new Error('Missing loan result panel');
          resultCache.set(targetUrl, resultMarkup);
          return resultMarkup;
        })
        .finally(() => pendingLoads.delete(targetUrl));

      pendingLoads.set(targetUrl, request);
      return request;
    };

    const markSelectedOrganization = (targetUrl) => {
      loanFilterLinks.forEach((link) => {
        const selected = normalizeLoanUrl(link.href) === targetUrl;
        link.classList.toggle('active', selected);
        link.classList.remove('is-pending');
        if (selected) {
          link.setAttribute('aria-current', 'true');
        } else {
          link.removeAttribute('aria-current');
        }
      });
    };

    const clearPendingState = () => {
      loanBrowser.classList.remove('is-changing');
      loanResult.setAttribute('aria-busy', 'false');
      loanFilterLinks.forEach((link) => link.classList.remove('is-pending'));
    };

    const cancelPendingSelection = () => {
      selectionSequence++;
      window.clearTimeout(resultHeightTimer);
      loanResult.style.minHeight = '';
      loanResult.classList.remove('is-entering');
      clearPendingState();
    };

    const showOrganization = async (address, { pushHistory = true } = {}) => {
      const targetUrl = normalizeLoanUrl(address);
      const requestSequence = ++selectionSequence;
      const nextLink = loanFilterLinks.find((link) => normalizeLoanUrl(link.href) === targetUrl);

      loanBrowser.classList.add('is-changing');
      loanResult.setAttribute('aria-busy', 'true');
      nextLink?.classList.add('is-pending');
      loanResult.style.minHeight = `${loanResult.offsetHeight}px`;
      window.clearTimeout(resultHeightTimer);

      try {
        const resultMarkup = await loadResultMarkup(targetUrl);
        if (requestSequence !== selectionSequence) return;

        loanResult.innerHTML = resultMarkup;
        initializeLoanPagination(loanResult);
        markSelectedOrganization(targetUrl);

        if (pushHistory) {
          const targetAddress = new URL(targetUrl);
          window.history.pushState(
            { loanOrganization: targetAddress.search },
            '',
            `${targetAddress.pathname}${targetAddress.search}${targetAddress.hash}`
          );
        }

        loanResult.classList.remove('is-entering');
        window.requestAnimationFrame(() => loanResult.classList.add('is-entering'));
        resultHeightTimer = window.setTimeout(() => {
          loanResult.style.minHeight = '';
          loanResult.classList.remove('is-entering');
        }, 190);
      } catch (error) {
        if (requestSequence === selectionSequence) {
          window.location.assign(targetUrl);
        }
      } finally {
        if (requestSequence === selectionSequence) {
          clearPendingState();
        }
      }
    };

    loanFilterLinks.forEach((link) => {
      link.addEventListener('pointerenter', () => {
        loadResultMarkup(link.href).catch(() => {});
      }, { once: true });
      link.addEventListener('focus', () => {
        loadResultMarkup(link.href).catch(() => {});
      }, { once: true });
      link.addEventListener('click', (event) => {
        if (
          event.defaultPrevented
          || event.button !== 0
          || event.metaKey
          || event.ctrlKey
          || event.shiftKey
          || event.altKey
        ) return;

        event.preventDefault();
        if (link.classList.contains('active')) {
          if (loanBrowser.classList.contains('is-changing')) {
            cancelPendingSelection();
          }
          return;
        }
        showOrganization(link.href);
      });
    });

    const prefetchInactiveOrganizations = () => {
      if (loanFilterLinks.length > 8) return;
      loanFilterLinks
        .filter((link) => !link.classList.contains('active'))
        .forEach((link, index) => {
          window.setTimeout(() => {
            loadResultMarkup(link.href).catch(() => {});
          }, index * 90);
        });
    };

    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(prefetchInactiveOrganizations, { timeout: 1200 });
    } else {
      window.setTimeout(prefetchInactiveOrganizations, 420);
    }

    window.addEventListener('popstate', () => {
      if (window.location.pathname === '/loan-groups') {
        showOrganization(window.location.href, { pushHistory: false });
      }
    });
  }
}

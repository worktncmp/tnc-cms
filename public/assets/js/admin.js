(() => {
    const body = document.getElementById("page-body");
    const form = document.getElementById("page-editor-form");
    const wrap = document.querySelector(".wysiwyg-wrap");
    const bodyError = document.getElementById("page-body-error");
    const pathInput = document.getElementById("page-path");
    const titleInput = document.getElementById("page-title");
    const pathPreview = document.getElementById("page-path-preview");
    const pathError = document.getElementById("page-path-error");

    let pathTouched = Boolean(pathInput?.value);
    let mediaPicker = null;
    let mediaPickerGrid = null;
    let mediaPickerEmpty = null;
    let mediaPickerCallback = null;
    let mediaCache = null;
    let activeEditor = null;

    const existingPaths = (() => {
        if (!form) {
            return new Set();
        }
        try {
            const raw = form.getAttribute("data-existing-paths") || "[]";
            return new Set(JSON.parse(raw));
        } catch (error) {
            return new Set();
        }
    })();

    const mediaListUrl =
        form?.getAttribute("data-media-list-url") ||
        wrap?.getAttribute("data-media-list-url") ||
        "/admin/media/list.json";

    const slugify = (value) =>
        value
            .toLowerCase()
            .trim()
            .replace(/['']/g, "")
            .replace(/[^a-z0-9\s-/]/g, "")
            .replace(/\s+/g, "-")
            .replace(/-+/g, "-")
            .replace(/^-+|-+$/g, "")
            .replace(/\/+/g, "/");

    const syncEditor = () => {
        if (window.tinymce) {
            window.tinymce.triggerSave();
        }
    };

    const bodyIsEmpty = () => {
        syncEditor();
        const html = (body?.value || "").trim();
        if (html === "") {
            return true;
        }
        const text = html.replace(/<[^>]+>/g, "").replace(/&nbsp;/g, " ").trim();
        return text === "";
    };

    const showBodyError = (show) => {
        if (bodyError) {
            bodyError.hidden = !show;
        }
    };

    const showPathError = (message) => {
        if (!pathError) {
            return;
        }
        if (message) {
            pathError.textContent = message;
            pathError.hidden = false;
            pathInput?.setAttribute("aria-invalid", "true");
        } else {
            pathError.hidden = true;
            pathInput?.removeAttribute("aria-invalid");
        }
    };

    const updatePathPreview = () => {
        if (!pathPreview || !pathInput) {
            return;
        }
        const path = pathInput.value.trim().replace(/^\/+/, "");
        if (path === "") {
            pathPreview.innerHTML = "Path updates as you type the title";
            return;
        }
        pathPreview.innerHTML = 'Will be published at <strong>/' + path + "</strong>";
    };

    const validatePath = () => {
        if (!pathInput) {
            return true;
        }
        const path = pathInput.value.trim().replace(/^\/+/, "");
        const pattern = /^[a-z0-9]+(-[a-z0-9]+)*(\/[a-z0-9]+(-[a-z0-9]+)*)*$/;
        if (path === "") {
            showPathError("Enter a URL path for this page.");
            return false;
        }
        if (!pattern.test(path)) {
            showPathError("Use lowercase letters, numbers, and hyphens only.");
            return false;
        }
        if (existingPaths.has(path)) {
            showPathError("A page already exists at /" + path + ". Choose a different path.");
            return false;
        }
        showPathError("");
        return true;
    };

    if (pathInput) {
        pathInput.addEventListener("input", () => {
            pathTouched = true;
            pathInput.value = slugify(pathInput.value);
            updatePathPreview();
            showPathError("");
        });
    }

    if (titleInput && pathInput) {
        titleInput.addEventListener("input", () => {
            if (!pathTouched) {
                pathInput.value = slugify(titleInput.value);
                updatePathPreview();
                showPathError("");
            }
        });
    }

    const ensureMediaPicker = () => {
        if (mediaPicker) {
            return mediaPicker;
        }

        mediaPicker = document.createElement("div");
        mediaPicker.id = "media-picker";
        mediaPicker.className = "media-picker";
        mediaPicker.hidden = true;
        mediaPicker.setAttribute("aria-hidden", "true");
        mediaPicker.innerHTML =
            '<div class="media-picker-backdrop" data-close-picker></div>' +
            '<div class="media-picker-panel" role="dialog" aria-modal="true" aria-labelledby="media-picker-title">' +
            '<div class="media-picker-header">' +
            '<h2 id="media-picker-title">Choose an image</h2>' +
            '<button type="button" class="button-quiet" data-close-picker>Close</button>' +
            "</div>" +
            '<p class="muted media-picker-empty" hidden>No images yet. Upload some in the Media library first.</p>' +
            '<div class="media-picker-grid"></div>' +
            "</div>";

        document.body.appendChild(mediaPicker);
        mediaPickerGrid = mediaPicker.querySelector(".media-picker-grid");
        mediaPickerEmpty = mediaPicker.querySelector(".media-picker-empty");

        mediaPicker.querySelectorAll("[data-close-picker]").forEach((el) => {
            el.addEventListener("click", (event) => {
                event.preventDefault();
                closeMediaPicker();
            });
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && mediaPicker && mediaPicker.classList.contains("is-open")) {
                closeMediaPicker();
            }
        });

        return mediaPicker;
    };

    const closeMediaPicker = () => {
        if (!mediaPicker) {
            return;
        }
        mediaPicker.classList.remove("is-open");
        mediaPicker.hidden = true;
        mediaPicker.setAttribute("aria-hidden", "true");
        document.body.classList.remove("media-picker-open");
        mediaPickerCallback = null;
    };

    const openMediaPicker = (callback) => {
        if (typeof callback !== "function") {
            return;
        }

        ensureMediaPicker();
        mediaPickerCallback = callback;
        mediaPicker.hidden = false;
        mediaPicker.classList.add("is-open");
        mediaPicker.setAttribute("aria-hidden", "false");
        document.body.classList.add("media-picker-open");
        renderMediaPicker();
    };

    const renderMediaPicker = async () => {
        if (!mediaPickerGrid || !mediaPickerEmpty) {
            return;
        }

        mediaPickerGrid.innerHTML = '<p class="muted">Loading images…</p>';

        try {
            if (!mediaCache) {
                const response = await fetch(mediaListUrl, {
                    headers: { Accept: "application/json" },
                    credentials: "same-origin",
                });
                if (!response.ok) {
                    throw new Error("Could not load media");
                }
                const data = await response.json();
                mediaCache = Array.isArray(data.files) ? data.files : [];
            }

            if (mediaCache.length === 0) {
                mediaPickerGrid.innerHTML = "";
                mediaPickerEmpty.hidden = false;
                return;
            }

            mediaPickerEmpty.hidden = true;
            mediaPickerGrid.innerHTML = mediaCache
                .map((file) => {
                    const name = file.name || "Image";
                    const url = file.url || "";
                    return (
                        '<button type="button" class="media-picker-item" data-url="' +
                        url +
                        '"><img src="' +
                        url +
                        '" alt="' +
                        name +
                        '" loading="lazy"><span>' +
                        name +
                        "</span></button>"
                    );
                })
                .join("");

            mediaPickerGrid.querySelectorAll("[data-url]").forEach((button) => {
                button.addEventListener("click", () => {
                    const url = button.getAttribute("data-url") || "";
                    const callback = mediaPickerCallback;
                    closeMediaPicker();
                    if (callback && url) {
                        callback(url);
                    }
                });
            });
        } catch (error) {
            mediaPickerGrid.innerHTML =
                '<p class="field-error">Could not load images. Upload in Media, then paste the URL manually.</p>';
        }
    };

    const insertImage = (url) => {
        const safeUrl = url.trim();
        if (safeUrl === "") {
            return;
        }

        if (activeEditor) {
            activeEditor.insertContent('<img src="' + safeUrl + '" alt="">');
            activeEditor.focus();
            showBodyError(false);
            return;
        }

        if (body) {
            const tag = '<img src="' + safeUrl + '" alt="">';
            body.setRangeText(tag, body.selectionStart, body.selectionEnd, "end");
            body.focus();
            showBodyError(false);
        }
    };

    if (form && body) {
        form.addEventListener("submit", (event) => {
            syncEditor();
            let valid = true;

            if (pathInput && !validatePath()) {
                valid = false;
                pathInput.focus();
            }

            if (bodyIsEmpty()) {
                valid = false;
                showBodyError(true);
                if (window.tinymce?.get("page-body")) {
                    window.tinymce.get("page-body").focus();
                } else {
                    body.focus();
                }
            } else {
                showBodyError(false);
            }

            if (!valid) {
                event.preventDefault();
            }
        });
    }

    const initTinyMce = () => {
        if (!body || !window.tinymce || !wrap) {
            return;
        }

        window.tinymce.init({
            selector: "#page-body",
            base_url: "https://cdn.jsdelivr.net/npm/tinymce@7.6.0",
            suffix: ".min",
            license_key: "gpl",
            menubar: "edit insert format table",
            plugins: "lists link table code autoresize",
            toolbar:
                "undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link medialibrary | table | code removeformat",
            branding: false,
            promotion: false,
            height: 480,
            min_height: 360,
            autoresize_bottom_margin: 24,
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            content_style:
                "body { font-family: Georgia, 'Times New Roman', serif; font-size: 16px; line-height: 1.6; color: #1c1914; max-width: 42rem; margin: 1rem auto; padding: 0 1rem; }",
            style_formats: [
                { title: "Paragraph", block: "p" },
                { title: "Heading 2", block: "h2" },
                { title: "Heading 3", block: "h3" },
                { title: "Lead", block: "p", classes: "lead" },
            ],
            setup: (editor) => {
                activeEditor = editor;

                editor.ui.registry.addButton("medialibrary", {
                    icon: "image",
                    tooltip: "Insert image from Media library",
                    onAction: () => {
                        openMediaPicker(insertImage);
                    },
                });

                editor.on("change keyup", () => {
                    showBodyError(false);
                });

                editor.on("remove", () => {
                    if (activeEditor === editor) {
                        activeEditor = null;
                    }
                });
            },
        });
    };

    if (body && wrap) {
        const script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/npm/tinymce@7.6.0/tinymce.min.js";
        script.referrerPolicy = "origin";
        script.onload = initTinyMce;
        script.onerror = () => {
            const toolbar = document.createElement("div");
            toolbar.className = "editor-toolbar";
            toolbar.innerHTML = [
                '<button type="button" class="button-quiet" data-wrap="<strong>" data-wrap-end="</strong>">Bold</button>',
                '<button type="button" class="button-quiet" data-wrap="<em>" data-wrap-end="</em>">Italic</button>',
                '<button type="button" class="button-quiet" data-wrap="<h2>" data-wrap-end="</h2>">Heading</button>',
                '<button type="button" class="button-quiet" data-wrap="<p>" data-wrap-end="</p>">Paragraph</button>',
                '<button type="button" class="button-quiet" data-wrap=\'<a href="https://">\' data-wrap-end="</a>">Link</button>',
                '<button type="button" class="button-quiet" data-action="image">Image</button>',
            ].join("");
            body.parentNode.insertBefore(toolbar, body);
            const note = document.createElement("p");
            note.className = "muted";
            note.textContent = "Visual editor could not load (CDN blocked). Using simple HTML tools.";
            body.parentNode.insertBefore(note, toolbar);

            toolbar.querySelectorAll("[data-wrap]").forEach((button) => {
                button.addEventListener("click", () => {
                    const start = button.getAttribute("data-wrap") || "";
                    const end = button.getAttribute("data-wrap-end") || "";
                    const from = body.selectionStart;
                    const to = body.selectionEnd;
                    const selected = body.value.slice(from, to) || "text";
                    body.setRangeText(start + selected + end, from, to, "select");
                    body.focus();
                });
            });
            toolbar.querySelector('[data-action="image"]')?.addEventListener("click", () => {
                openMediaPicker(insertImage);
            });
        };
        document.head.appendChild(script);
    }

    updatePathPreview();

    document.querySelectorAll(".js-copy-url").forEach((button) => {
        button.addEventListener("click", async () => {
            const value = button.getAttribute("data-url") || "";
            try {
                await navigator.clipboard.writeText(value);
                button.textContent = "Copied";
                setTimeout(() => {
                    button.textContent = "Copy URL";
                }, 1200);
            } catch (error) {
                window.prompt("Copy this URL", value);
            }
        });
    });

    document.querySelectorAll(".js-insert-img").forEach((button) => {
        button.addEventListener("click", async () => {
            const value = button.getAttribute("data-url") || "";
            const tag = '<img src="' + value + '" alt="">';
            try {
                await navigator.clipboard.writeText(tag);
                button.textContent = "Copied";
                setTimeout(() => {
                    button.textContent = "Img tag";
                }, 1200);
            } catch (error) {
                window.prompt("Copy this tag", tag);
            }
        });
    });
})();

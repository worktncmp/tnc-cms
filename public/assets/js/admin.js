(() => {
    const body = document.getElementById("page-body");
    const form = document.getElementById("page-editor-form");
    const wrap = document.querySelector(".wysiwyg-wrap");
    const bodyError = document.getElementById("page-body-error");
    const pathInput = document.getElementById("page-path");

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

    if (pathInput) {
        pathInput.addEventListener("input", () => {
            pathInput.value = pathInput.value
                .toLowerCase()
                .replace(/\s+/g, "-")
                .replace(/[^a-z0-9\-/]/g, "")
                .replace(/-+/g, "-")
                .replace(/\/+/g, "/");
        });
    }

    if (form && body) {
        form.addEventListener("submit", (event) => {
            syncEditor();
            if (bodyIsEmpty()) {
                event.preventDefault();
                showBodyError(true);
                if (window.tinymce?.get("page-body")) {
                    window.tinymce.get("page-body").focus();
                } else {
                    body.focus();
                }
                return;
            }
            showBodyError(false);
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
            plugins: "lists link image table code autoresize",
            toolbar:
                "undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | table | code removeformat",
            branding: false,
            promotion: false,
            height: 420,
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            content_style:
                "body { font-family: Georgia, 'Times New Roman', serif; font-size: 16px; line-height: 1.6; color: #1c1914; }",
            file_picker_types: "image",
            file_picker_callback: (callback, _value, meta) => {
                if (meta.filetype !== "image") {
                    return;
                }
                const mediaUrl = wrap.getAttribute("data-media-url") || "/admin/media";
                const hint =
                    "Paste an image URL from the Media library.\n\nOpen Media in another tab if needed:\n" +
                    mediaUrl;
                const url = window.prompt(hint, wrap.getAttribute("data-uploads-base") || "");
                if (url) {
                    callback(url, { alt: "" });
                }
            },
            setup: (editor) => {
                editor.on("change keyup", () => {
                    showBodyError(false);
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
                '<button type="button" class="button-quiet" data-insert=\'<img src="" alt="">\'>Image</button>',
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
            toolbar.querySelectorAll("[data-insert]").forEach((button) => {
                button.addEventListener("click", () => {
                    const snippet = button.getAttribute("data-insert") || "";
                    const url = window.prompt("Image URL (from Media library)", "");
                    if (url === null) {
                        return;
                    }
                    const tag = snippet.includes('src=""')
                        ? snippet.replace('src=""', 'src="' + url + '"')
                        : snippet;
                    body.setRangeText(tag, body.selectionStart, body.selectionEnd, "end");
                    body.focus();
                });
            });
        };
        document.head.appendChild(script);
    }

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

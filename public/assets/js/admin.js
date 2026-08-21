(() => {
    const body = document.getElementById("page-body");
    if (body) {
        document.querySelectorAll(".editor-toolbar [data-wrap]").forEach((button) => {
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

        document.querySelectorAll(".editor-toolbar [data-insert]").forEach((button) => {
            button.addEventListener("click", () => {
                const snippet = button.getAttribute("data-insert") || "";
                const url = window.prompt("Image URL (from Media library)", "");
                if (url === null) {
                    return;
                }
                const tag = snippet.includes('src=""')
                    ? snippet.replace('src=""', 'src="' + url + '"')
                    : snippet;
                const from = body.selectionStart;
                body.setRangeText(tag, from, body.selectionEnd, "end");
                body.focus();
            });
        });
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

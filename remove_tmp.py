from pathlib import Path

def remove_last_modal_script(src):
    path = Path(src)
    text = path.read_text()
    marker = '<script>\n(function() {'
    start = text.rfind(marker)
    if start == -1:
        raise SystemExit('Marker not found')
    end = text.index('</script>', start) + len('</script>')
    new_text = text[:start] + '\n<?php endif; ?>\n' if text[end:].lstrip().startswith('<?php endif; ?>') else None


document.addEventListener('DOMContentLoaded', function() {
    // Toggle children visibility
    document.querySelectorAll('.toggle-children').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const li = this.closest('li');
            const ul = li.querySelector('ul');
            const icon = this.querySelector('.toggle-icon');
            
            if (ul) {
                ul.classList.toggle('collapsed');
                icon.classList.toggle('rotated');
                
                // Update tooltip
                const isCollapsed = ul.classList.contains('collapsed');
                this.setAttribute('uk-tooltip', isCollapsed ? 'Expand children' : 'Collapse children');
            }
        });
    });

    // Expand All
    document.getElementById('expandAll')?.addEventListener('click', function() {
        document.querySelectorAll('.tree ul').forEach(ul => {
            ul.classList.remove('collapsed');
        });
        document.querySelectorAll('.toggle-icon').forEach(icon => {
            icon.classList.remove('rotated');
        });
    });

    // Collapse All
    document.getElementById('collapseAll')?.addEventListener('click', function() {
        document.querySelectorAll('.tree ul').forEach(ul => {
            if (ul.closest('li')?.dataset?.level !== '0') {
                ul.classList.add('collapsed');
            }
        });
        document.querySelectorAll('.toggle-icon').forEach(icon => {
            icon.classList.add('rotated');
        });
    });

    // Center Tree
    document.getElementById('centerTree')?.addEventListener('click', function() {
        const treeWrappers = document.querySelectorAll('.tree-wrapper');
        treeWrappers.forEach(wrapper => {
            const tree = wrapper.querySelector('.tree');
            if (tree) {
                tree.scrollLeft = (tree.scrollWidth - tree.clientWidth) / 2;
            }
        });
    });

    // Initialize tooltips
    if (typeof UIkit !== 'undefined') {
        UIkit.util.ready(function() {
            UIkit.tooltip(document.querySelectorAll('[uk-tooltip]'));
        });
    }

    // Auto-center trees on load
    setTimeout(() => {
        if (document.querySelector('.tree-wrapper')) {
            document.getElementById('centerTree')?.click();
        }
    }, 500);
});
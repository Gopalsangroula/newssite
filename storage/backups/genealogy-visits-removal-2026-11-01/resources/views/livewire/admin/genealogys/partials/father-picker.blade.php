{{--
    Searchable father picker: there are too many members for a select.
    Expects $fatherLabel, the current father's name. Asks searchFathers() as the admin types
    and writes the choice to father_id. wire:ignore keeps a re-render from clearing the search.
--}}
<label for="father-search" class="uk-form-label">Father</label>

<div class="ah-combo" wire:ignore x-data="ahFatherPicker(@js($fatherLabel ?? null))" x-on:click.outside="open = false">
    <div class="ah-combo-selected" x-show="label" x-cloak>
        <span class="ah-combo-value" x-text="label"></span>
        <div class="ah-row-actions">
            <button type="button" x-on:click="clear()">Change</button>
        </div>
    </div>

    <div x-show="! label" x-cloak>
        <input
            id="father-search"
            x-ref="input"
            type="text"
            class="uk-input"
            placeholder="Type a name or pusta number..."
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            aria-controls="father-options"
            x-bind:aria-expanded="open"
            x-model="query"
            x-on:input="search()"
            x-on:focus="open = results.length > 0"
            x-on:keydown.down.prevent="move(1)"
            x-on:keydown.up.prevent="move(-1)"
            x-on:keydown.enter.prevent="chooseActive()"
            x-on:keydown.escape="open = false"
        >

        <ul id="father-options" class="ah-combo-list" role="listbox" x-show="open" x-cloak>
            <template x-for="(member, index) in results" x-bind:key="member.id">
                <li role="option" x-bind:aria-selected="index === active">
                    <button
                        type="button"
                        class="ah-combo-option"
                        x-bind:class="{ 'is-active': index === active }"
                        x-on:click="pick(member)"
                        x-on:mouseenter="active = index"
                    >
                        <span x-text="member.name"></span>
                        <span class="ah-table-meta" x-text="describe(member)"></span>
                    </button>
                </li>
            </template>
            <li class="ah-combo-note" x-show="results.length === 0">No members found.</li>
            <li class="ah-combo-note" x-show="hasMore">Showing the first 20. Type more to narrow the list.</li>
        </ul>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ahFatherPicker', (label) => ({
            label,
            query: '',
            results: [],
            hasMore: false,
            open: false,
            active: -1,
            timer: null,
            latestSearch: 0,

            search() {
                clearTimeout(this.timer);
                const term = this.query.trim();

                if (term.length < 2) {
                    this.latestSearch++;
                    this.results = [];
                    this.hasMore = false;
                    this.open = false;
                    return;
                }

                this.timer = setTimeout(async () => {
                    const thisSearch = ++this.latestSearch;
                    const found = await this.$wire.searchFathers(term);

                    // A slower, older answer must not replace a newer one
                    if (thisSearch !== this.latestSearch) return;

                    this.results = found.results;
                    this.hasMore = found.hasMore;
                    this.active = found.results.length ? 0 : -1;
                    this.open = true;
                }, 300);
            },

            move(step) {
                if (! this.results.length) return;
                this.open = true;
                this.active = (this.active + step + this.results.length) % this.results.length;
            },

            chooseActive() {
                if (this.open && this.results[this.active]) {
                    this.pick(this.results[this.active]);
                }
            },

            pick(member) {
                this.label = `${member.name} (Generation ${member.pustanumber})`;
                this.open = false;
                this.$wire.set('father_id', member.id, false);
            },

            clear() {
                this.label = null;
                this.query = '';
                this.results = [];
                this.hasMore = false;
                this.$wire.set('father_id', null, false);
                this.$nextTick(() => this.$refs.input.focus());
            },

            describe(member) {
                const father = member.father ? ` · Father: ${member.father}` : '';
                return `Generation ${member.pustanumber}${father}`;
            },
        }));
    });
</script>

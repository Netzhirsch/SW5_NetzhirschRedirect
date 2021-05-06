{extends file='parent:frontend/index/index.tpl'}

{namespace name="plugins/netzhirsch/redirect"}

{block name='frontend_index_after_body'}
    <div class="netzhirsch_redirect--hidden">
        <div id="netzhirsch-title">
            {s name="frontend/content/redirect/title"}Weiterleitung{/s}
        </div>
        <div id="netzhirsch-content">
            <div class="panel">
                <div class="panel--body">
                    <h3>{s name="frontend/content/redirect/question"}Möchten Sie weitergeleitet werden?{/s}</h3>
                    <div class="panel--actions">
                        <a class="btn is--primary netzhirsch-redirect" data-netzhirsch-redirect="1">
                            {s name="frontend/content/redirect/yes"}ja{/s}
                        </a>
                        <a class="btn is--secondary netzhirsch-redirect"  data-netzhirsch-redirect="0">
                            {s name="frontend/content/redirect/no"}nein{/s}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name="frontend_index_header_javascript_inline"}
    {$smarty.block.parent}
    let withoutConfirmation = '{$withoutConfirmation}';
    let active = '{$active}';
{/block}

{block name="frontend_index_start"}{action module=widgets controller=redirect action=index}{/block}

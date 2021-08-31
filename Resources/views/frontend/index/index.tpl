{extends file='parent:frontend/index/index.tpl'}

{block name='frontend_index_after_body'}
    <div class="netzhirsch_redirect--hidden">
        <div id="netzhirsch-title">
            {s namespace="frontend/plugins/netzhirschRedirect" name="popupTitle"}Weiterleitung{/s}
        </div>
        <div id="netzhirsch-content">
            <div class="panel">
                <div class="panel--body">
                    <p>{s namespace="frontend/plugins/netzhirschRedirect" name="popupQuestion"}Sie scheinen aus einem anderen Land zu kommen.<br>Möchten Sie zu unserem entsprechenden Shop weitergeleitet werden?{/s}</p>
                    <p>{s namespace="frontend/plugins/netzhirschRedirect" name="popupLocal"}Shop für {/s}{$local}</p>
                    <p>{s namespace="frontend/plugins/netzhirschRedirect" name="popupLanguage"}mit der Sprache {/s}{$language}</p>
                    <div class="panel--actions">
                        <a class="btn is--primary netzhirsch-redirect" data-netzhirsch-redirect="1">
                            {s namespace="frontend/plugins/netzhirschRedirect" name="popupConfirmButton"}ja{/s}
                        </a>
                        <a class="btn is--secondary netzhirsch-redirect"  data-netzhirsch-redirect="0">
                            {s namespace="frontend/plugins/netzhirschRedirect" name="popupDeclineButton"}nein{/s}
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

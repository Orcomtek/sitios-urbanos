import os
import re

files = [
    "resources/js/Pages/Tenant/Admin/Core/AdminWorkQueue.vue",
    "resources/js/Pages/Tenant/Resident/Governance/Pqrs.vue",
    "resources/js/Pages/Tenant/Admin/Ecosystem/Providers/Index.vue",
    "resources/js/Pages/Tenant/Resident/Dashboard.vue",
    "resources/js/Pages/Tenant/Resident/Ecosystem/Index.vue",
    "resources/js/Pages/Tenant/Resident/Ecosystem/Providers/Index.vue",
    "resources/js/Pages/Tenant/Resident/Core/Operations.vue"
]

for f in files:
    with open(f, "r") as file:
        content = file.read()
    
    # 1. Add usePage import if missing
    if "usePage" not in content and "<script setup" in content:
        content = re.sub(r"import\s+\{([^}]+)\}\s+from\s+'@inertiajs/vue3';", lambda m: m.group(0) if 'usePage' in m.group(1) else f"import {{{m.group(1)}, usePage}} from '@inertiajs/vue3';", content)
    
    # 2. Add communitySlug if missing
    if "communitySlug" not in content:
        if "const page =" in content:
            content = content.replace("const page = usePage();", "const page = usePage();\nconst communitySlug = (page.props.tenant as any)?.community?.slug;")
        else:
            content = re.sub(r'(<script setup[^>]*>.*?)(const |let |function )', r'\1const page = usePage();\nconst communitySlug = (page.props.tenant as any)?.community?.slug;\n\n\2', content, count=1, flags=re.DOTALL)

    # 3. Replace routes
    # AdminWorkQueue
    content = content.replace("`/api/ecosystem/listings/${id}/moderate`", "route('api.ecosystem.listings.moderate', { community_slug: communitySlug, listing: id })")
    
    # Pqrs
    content = content.replace("'/api/governance/pqrs'", "route('api.governance.pqrs.index', { community_slug: communitySlug })")
    content = content.replace("axios.post(route('api.governance.pqrs.index', { community_slug: communitySlug })", "axios.post(route('api.governance.pqrs.store', { community_slug: communitySlug })")
    
    # Providers
    content = content.replace("'/api/system/taxonomies/provider_category'", "route('api.system.taxonomies.index', { community_slug: communitySlug, type: 'provider_category' })")
    
    # Dashboard
    content = content.replace("`/api/finance/invoices/${invoiceId}/pay`", "route('api.finance.invoices.pay', { community_slug: communitySlug, invoice: invoiceId })")
    content = content.replace("'/api/cockpit/resident'", "route('api.cockpit.resident', { community_slug: communitySlug })")
    
    # Ecosystem
    content = content.replace("'/api/ecosystem/listings'", "route('api.ecosystem.listings.index', { community_slug: communitySlug })")
    content = content.replace("axios.post(route('api.ecosystem.listings.index', { community_slug: communitySlug })", "axios.post(route('api.ecosystem.listings.store', { community_slug: communitySlug })")
    content = content.replace("`/api/ecosystem/listings/${editingListing.value.id}`", "route('api.ecosystem.listings.update', { community_slug: communitySlug, listing: editingListing.value.id })")
    content = content.replace("`/api/ecosystem/listings/${listing.id}`", "route('api.ecosystem.listings.update', { community_slug: communitySlug, listing: listing.id })")
    
    # Operations
    content = content.replace("'/api/security/invitations'", "route('api.security.invitations.index', { community_slug: communitySlug })")
    content = content.replace("axios.post(route('api.security.invitations.index', { community_slug: communitySlug })", "axios.post(route('api.security.invitations.store', { community_slug: communitySlug })")
    content = content.replace("`/api/security/invitations/${invitationId}/revoke`", "route('api.security.invitations.revoke', { community_slug: communitySlug, invitation: invitationId })")
    
    content = content.replace("'/api/security/visitors'", "route('api.security.visitors.index', { community_slug: communitySlug })")
    content = content.replace("axios.post(route('api.security.visitors.index', { community_slug: communitySlug })", "axios.post(route('api.security.visitors.store', { community_slug: communitySlug })")
    
    content = content.replace("'/api/security/packages'", "route('api.security.packages.index', { community_slug: communitySlug })")

    with open(f, "w") as file:
        file.write(content)

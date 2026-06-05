const fs = require('fs');

function replaceFile(filePath, replacements) {
    if (!fs.existsSync(filePath)) {
        console.log('Skipping missing file: ' + filePath);
        return;
    }
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;
    
    for (const { regex, replace } of replacements) {
        content = content.replace(regex, replace);
    }
    
    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log('Updated: ' + filePath);
    }
}

// 1. Update PrimaryButton.vue
replaceFile('resources/js/Components/PrimaryButton.vue', [
    { regex: /bg-gray-800/g, replace: 'bg-emerald-600' },
    { regex: /hover:bg-gray-700/g, replace: 'hover:bg-emerald-700' },
    { regex: /focus:bg-gray-700/g, replace: 'focus:bg-emerald-700' },
    { regex: /active:bg-gray-900/g, replace: 'active:bg-emerald-800' },
    { regex: /focus:ring-indigo-500/g, replace: 'focus:ring-emerald-500' },
    { regex: /transition duration-150/g, replace: 'transition-colors duration-150' },
]);

// 2. Modals with slate/indigo buttons
const modalFiles = [
    'resources/js/Pages/Tenant/Admin/Core/Residents/Components/ResidentFormModal.vue',
    'resources/js/Pages/Tenant/Admin/Core/Units/Components/UnitFormModal.vue',
    'resources/js/Pages/Tenant/Admin/Core/Units/Components/GeneratorForm.vue',
    'resources/js/Pages/Tenant/Admin/Core/Units/Components/VisualMatrix.vue',
    'resources/js/Pages/Tenant/Admin/Core/Units/Form.vue',
    'resources/js/Pages/Tenant/Admin/Core/Residents/Form.vue',
];

const buttonReplacements = [
    { 
        regex: /bg-slate-900([\s\S]*?)hover:bg-slate-800([\s\S]*?)focus-visible:outline-slate-900/g, 
        replace: 'bg-emerald-600$1hover:bg-emerald-700$2focus:ring-emerald-500 focus-visible:outline-emerald-500 transition-colors' 
    },
    { 
        regex: /bg-indigo-600([\s\S]*?)hover:bg-indigo-500([\s\S]*?)focus-visible:outline-indigo-600/g, 
        replace: 'bg-emerald-600$1hover:bg-emerald-700$2focus:ring-emerald-500 focus-visible:outline-emerald-500 transition-colors' 
    }
];

modalFiles.forEach(f => replaceFile(f, buttonReplacements));

// 3. Header Icons (Tickets & Radar)
const headerReplacements = [
    { regex: /bg-indigo-100/g, replace: 'bg-emerald-100' },
    { regex: /text-indigo-600/g, replace: 'text-emerald-600' }
];

replaceFile('resources/js/Pages/Tenant/Admin/Governance/Tickets/Index.vue', headerReplacements);
replaceFile('resources/js/Pages/Tenant/Admin/Security/Radar/Index.vue', headerReplacements);

// 4. Chat (Tickets/Show.vue)
replaceFile('resources/js/Pages/Tenant/Admin/Governance/Tickets/Show.vue', [
    // Chat bubble
    { regex: /'bg-slate-900 text-white rounded-tr-sm'/g, replace: "'bg-emerald-600 text-white rounded-tr-sm'" },
    // Textarea focus
    { regex: /focus:border-slate-900/g, replace: 'focus:border-emerald-500' },
    { regex: /focus:ring-slate-900/g, replace: 'focus:ring-emerald-500' },
    // Enviar Button
    { regex: /bg-slate-900 border border-transparent/g, replace: 'bg-emerald-600 border border-transparent' },
    { regex: /hover:bg-slate-800/g, replace: 'hover:bg-emerald-700' },
    { regex: /focus:bg-slate-900/g, replace: 'focus:bg-emerald-700' },
    { regex: /active:bg-indigo-900/g, replace: 'active:bg-emerald-800' },
    // Inject disabled:bg-emerald-300 next to disabled:opacity-50
    { regex: /disabled:opacity-50/g, replace: 'disabled:opacity-50 disabled:bg-emerald-300' }
]);

// 5. Imports (Core/Imports/Index.vue)
replaceFile('resources/js/Pages/Tenant/Admin/Core/Imports/Index.vue', [
    { regex: /disabled:opacity-50/g, replace: 'disabled:bg-emerald-300 disabled:opacity-50 transition-colors' }
]);

console.log('Unification script complete.');

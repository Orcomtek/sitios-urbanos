const datePickerFormat = (date) => {
    if (!date) return '';
    try {
        let d;
        if (typeof date === 'string') {
            // Append time so we don't shift timezone if it's yyyy-MM-dd
            d = new Date(date.includes('T') ? date : date + 'T12:00:00Z');
        } else {
            d = new Date(date);
        }
        
        const day = d.getDate().toString().padStart(2, '0');
        const month = (d.getMonth() + 1).toString().padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    } catch {
        return String(date);
    }
};
console.log(datePickerFormat("2026-06-09"));

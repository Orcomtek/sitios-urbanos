const timePickerFormat = (time) => {
    if (!time) return '';
    let hours, minutes;
    
    // Check if it's a string like "HH:mm"
    if (typeof time === 'string') {
        const parts = time.split(':');
        if (parts.length >= 2) {
            hours = parseInt(parts[0], 10);
            minutes = parseInt(parts[1], 10);
        } else {
            return time;
        }
    } 
    // Check if it's an object { hours, minutes }
    else if (time && time.hours !== undefined && time.minutes !== undefined) {
        hours = time.hours;
        minutes = time.minutes;
    } 
    // Check if it's a Date object
    else if (time instanceof Date) {
        hours = time.getHours();
        minutes = time.getMinutes();
    } else {
        return String(time);
    }
    
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    hours = hours.toString().padStart(2, '0');
    minutes = minutes.toString().padStart(2, '0');
    return `${hours}:${minutes} ${ampm}`;
};

console.log(timePickerFormat("10:57"));
console.log(timePickerFormat({ hours: 14, minutes: 30 }));
console.log(timePickerFormat(new Date()));

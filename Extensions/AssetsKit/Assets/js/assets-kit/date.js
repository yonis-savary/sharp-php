/*
 ___   _ _____ ___     _   ___  ___
|   \ /_\_   _| __|   /_\ |   \|   \
| |) / _ \| | | _|   / _ \| |) | |) |
|___/_/ \_\_| |___| /_/ \_\___/|___/

This section of code allow you to work with dates
in a more "textual" way

Those constants are defined...
- SECOND[S]
- MINUTE[S]
- HOUR[S]
- DAY[S]
- WEEK[S]
- MONTH[S]
- YEAR[S]

...And they allow you to "add" interval of time to a date

```js
    let oneWeekFromNow = (new Date).add(1, WEEK);

    let now = new Date;
    now.add(1, DAY)
    now.add(3, DAYS)
    now.add(-2, WEEKS)
    now.add(20984, MINUTES)
    now.add(-3, HOUR)
```

Note: `add` both mutate the date AND return it,
which mean that you can use it as a "standalone" instruction or an expression

```js
    let d = new Date();

    d.add(1, WEEK) // d value is changed
    let n = (new Date).add(1, WEEK).add(3, DAYS) // 10 DAYS added
```

*/



declareNewBridge("date", {

    LOCALE: "fr-FR",

    DATE_OPTIONS: {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric'
    },

    DATETIME_OPTIONS: {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric',
        hour12: false
    },


    SECOND: "date.units.second", SECONDS: "date.units.second",
    MINUTE: "date.units.minute", MINUTES: "date.units.minute",
    HOUR:   "date.units.hour",   HOURS:   "date.units.hour",
    DAY:    "date.units.day",    DAYS:    "date.units.day",
    WEEK:   "date.units.week",   WEEKS:   "date.units.week",
    MONTH:  "date.units.month",  MONTHS:  "date.units.month",
    YEAR:   "date.units.year",   YEARS:   "date.units.year",


    /**
     * @param {Date|string} start1 Start of the first period
     * @param {Date|string} end1 End of the first period
     * @param {Date|string} start2 Start of the second period
     * @param {Date|string} end2 End of the second period
     * @returns Do these two period overlap
     * @note Two periods that "touch" (ex: P1 ends where P2 starts) are not considered as overlaping
     */
    doPeriodOverlap: function(start1, end1, start2, end2)
    {
        start1 = new Date(start1);
        start2 = new Date(start2);
        end1 = new Date(end1);
        end2 = new Date(end2);
        return (!(end1 <= start2 || start1 >= end2))
    },


    /**
     * @param {Date|string} start1 Start of the first period
     * @param {Date|string} end1 End of the first period
     * @param {Date|string} start2 Start of the second period
     * @param {Date|string} end2 End of the second period
     * @returns Do these two period touch
     */
    doPeriodTouch: function(start1, end1, start2, end2)
    {
        start1 = new Date(start1);
        start2 = new Date(start2);
        end1 = new Date(end1);
        end2 = new Date(end2);
        return (!(end1 < start2 || start1 > end2))
    },


    /**
     * @param {Date|string} date date to format
     * @returns A formatted date for THE USER (Don't build any data system based on this function !)
     */
    dateToString: function(date, dateTime=false)
    {
        let options = dateTime ? this.DATETIME_OPTIONS: this.DATE_OPTIONS
        return new Intl.DateTimeFormat(this.LOCALE, options).format(new Date(date));
    },


    /**
     * @param {Date|string} date Date to transform
     * @returns A date with the YYYY-MM-DD format
     */
    dateToSQL: function(date, dateTime=false)
    {
        let twoDigitsOf = n => n.toString().padStart(2, '0');
        let d = new Date(date);

        date = (
            d.getFullYear() + "-" +
            twoDigitsOf(d.getMonth()+1) + "-" +
            twoDigitsOf(d.getDate())
        );
        if (!dateTime)
            return date;

        return (
            date + " " +
            twoDigitsOf(d.getHours()) + ":" +
            twoDigitsOf(d.getMinutes()) + ":" +
            twoDigitsOf(d.getSeconds())
        );
    }


}, bridge => {return {
    doPeriodOverlap : bridge.doPeriodOverlap,
    doPeriodTouch : bridge.doPeriodTouch,
    dateToString : bridge.dateToString,
    dateToSQL : bridge.dateToSQL,
    SECOND: bridge.SECOND, SECONDS: bridge.SECOND,
    MINUTE: bridge.MINUTE, MINUTES: bridge.MINUTE,
    HOUR: bridge.HOUR,     HOURS: bridge.HOUR,
    DAY: bridge.DAY,       DAYS: bridge.DAY,
    WEEK: bridge.WEEK,     WEEKS: bridge.WEEK,
    MONTH: bridge.MONTH,   MONTHS: bridge.MONTH,
    YEAR: bridge.YEAR,     YEARS: bridge.YEAR,
}}, bridge => {

    /**
     * Add a time interval to the object, and return a reference to the same date
     * @param {int} value Integer value
     * @param {string} unit Unit from TIME_UNITS
     * @returns
     */
    Date.prototype.add = function(value, unit)
    {
        bridge.TIME_UNITS ??= [
            bridge.SECOND, bridge.SECONDS,
            bridge.MINUTE, bridge.MINUTES,
            bridge.HOUR,   bridge.HOURS,
            bridge.DAY,    bridge.DAYS,
            bridge.WEEK,   bridge.WEEKS,
            bridge.MONTH,  bridge.MONTHS,
            bridge.YEAR,   bridge.YEARS
        ];

        if (!Number.isFinite(value))
            throw Error(`[value] parameter must be a number, [${value}] given`);

        if (!bridge.TIME_UNITS.includes(unit))
        {
            unit = unit.toUpperCase();
            throw Error("Given unit is not in TIME_UNITS constant");
        }

        let d = this;

        switch (unit)
        {
            case SECOND:    d.setSeconds (d.getSeconds ()+value);     break;
            case MINUTE:    d.setMinutes (d.getMinutes ()+value);     break;
            case HOUR:      d.setHours   (d.getHours   ()+value);     break;
            case DAY:       d.setDate    (d.getDate    ()+value);     break;
            case WEEK:      d.setDate    (d.getDate    ()+(value*7)); break;
            case MONTH:     d.setMonth   (d.getMonth   ()+value);     break;
            case YEAR:      d.setFullYear(d.getFullYear()+value);     break;
        }

        return d;
    }


    Date.prototype.sub = function(value, unit)
    {
        return this.add(-value, unit);
    }


    Date.prototype.diff = function(date, type=null)
    {
        date = new Date(date);
        let delta = (Math.abs(date.getTime() - this.getTime()))/1000;
        let f = this < date ? 1 : -1;

        let diff = {
            negative: f < 0,
            [SECOND]: (f*delta) ,
            [MINUTE]: (f*delta) / 60,
            [HOUR]  : (f*delta) / 3600,
            [DAY]   : (f*delta) / (3600*24),
            [WEEK]  : (f*delta) / (3600*24*7),
            [MONTH] : (f*delta) / (3600*24*31),
            [YEAR]  : (f*delta) / (3600*24*365.25),
        }

        if (type == null)
            return diff;

        if (!(type in diff))
            throw new Error(`Unknown Interval type ! [${type}]`);

        return diff[type];
    }

    // Prototype shortcuts to previous functions
    Date.prototype.isBetween = function(from=null, to=null) {
        if (from && to)
        {
            from = new Date(from);
            to = new Date(to);
            return (from <= this && this <= to )
        }
        else if (from)
        {
            return (from <= this);
        }
        else if (to)
        {
            return (this <= to);
        }
        throw new Error("At least one of 'from' or 'to' parameter is needed");
    }


    /**
     * Reset the time of a date to midnight
     * (Can be used to compare two Date and not their time)
     */
    Date.prototype.resetTime = function(){
        this.setHours(0, 0, 0, 0);
        return this;
    }



    Date.prototype.sameDayAs = function(another){
        let thisDate = new Date(this);
        let anotherDate = new Date(another);

        thisDate.resetTime();
        anotherDate.resetTime();

        return thisDate.toISOString() == anotherDate.toISOString();
    }




});
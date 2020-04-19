package com.kalma.Data;

import org.joda.time.DateTime;

public class LineGraphEntry {
    private DateTime date;
    private float value;

    public LineGraphEntry(DateTime date, float value) {
        this.date = date;
        this.value = value;
    }

    public DateTime getDate() {
        return date;
    }

    public void setDate(DateTime date) {
        this.date = date;
    }

    public float getValue() {
        return value;
    }

    public void setValue(float value) {
        this.value = value;
    }
}

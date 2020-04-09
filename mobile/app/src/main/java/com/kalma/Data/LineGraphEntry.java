package com.kalma.Data;

import org.joda.time.DateTime;

public class LineGraphEntry {
    DateTime date;
    double value;

    public LineGraphEntry(DateTime date, double value) {
        this.date = date;
        this.value = value;
    }

    public DateTime getDate() {
        return date;
    }

    public void setDate(DateTime date) {
        this.date = date;
    }

    public double getValue() {
        return value;
    }

    public void setValue(double value) {
        this.value = value;
    }
}

package com.kalma.Data;

import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;

public class SleepDataEntry {
    int id;
    DateTime startTime;
    DateTime stopTime;
    int duration;
    String durationText;
    int sleepQuality;
    String message;
    int percentage;

    public SleepDataEntry() {
    }

    public SleepDataEntry(int id, DateTime startTime, DateTime stopTime, int duration, int sleepQuality) {
        this.id = id;
        this.startTime = startTime;
        this.stopTime = stopTime;
        this.duration = duration;
        this.sleepQuality = sleepQuality;
    }

    public int getPercentage() {
        return percentage;
    }

    public void setPercentage(int percentage) {
        this.percentage = percentage;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public DateTime getStartTime() {
        return startTime;
    }

    public void setStartTime(DateTime startTime) {
        this.startTime = startTime;
    }

    public DateTime getStopTime() {
        return stopTime;
    }

    public void setStopTime(DateTime stopTime) {
        this.stopTime = stopTime;
    }

    public int getDuration() {
        return duration;
    }

    public void setDuration(int duration) {
        this.duration = duration;
    }

    public String getDurationText() {
        return durationText;
    }

    public void setDurationText(String durationText) {
        this.durationText = durationText;
    }

    public int getSleepQuality() {
        return sleepQuality;
    }

    public void setSleepQuality(int sleepQuality) {
        this.sleepQuality = sleepQuality;
    }

    public String getStartISO() {
        return startTime.toString(DateTimeFormat.forPattern("yyyy-MM-dd'T'HH:mm:ss"));
    }

    public String getStopISO() {
        return stopTime.toString(DateTimeFormat.forPattern("yyyy-MM-dd'T'HH:mm:ss"));
    }
}

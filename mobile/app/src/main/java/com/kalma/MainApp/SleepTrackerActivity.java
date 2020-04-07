package com.kalma.MainApp;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

import com.github.mikephil.charting.charts.LineChart;
import com.github.mikephil.charting.data.Entry;
import com.kalma.Data.SleepDataEntry;
import com.kalma.R;

import org.joda.time.DateTime;

import java.util.ArrayList;
import java.util.List;

public class SleepTrackerActivity extends AppCompatActivity {

    Context context = this;
    Button buttonProfile, buttonSettings, buttonHome;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_sleep_tracker);

        buttonProfile = findViewById(R.id.btnProfile);
        buttonProfile.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, UserProfileActivity.class);
                startActivity(intent);
            }
        });
        buttonSettings = findViewById(R.id.btnSettings);
        buttonSettings.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, SettingsActivity.class);
                startActivity(intent);
            }
        });
        buttonHome = findViewById(R.id.btnHome);
        buttonHome.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, HomeActivity.class);
                startActivity(intent);
            }
        });


        LineChart chart = (LineChart) findViewById(R.id.chart);
        SleepDataEntry[] dataObjects = {new SleepDataEntry()};

        List<Entry> entries = new ArrayList<Entry>();
        for (SleepDataEntry data : dataObjects) {
            // turn your data into Entry objects
            //entries.add(new Entry(data.getStartTime(), data.getDuration()));


        }
    }
}









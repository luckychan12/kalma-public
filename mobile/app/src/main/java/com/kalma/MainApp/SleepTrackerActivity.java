package com.kalma.MainApp;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import android.app.DatePickerDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TimePicker;

import com.github.mikephil.charting.charts.LineChart;
import com.github.mikephil.charting.data.Entry;
import com.kalma.Data.AuthStrings;
import com.kalma.Data.SleepDataEntry;
import com.kalma.R;

import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import java.util.Locale;

public class SleepTrackerActivity extends AppCompatActivity {

    Context context = this;
    EditText txtStartDate, txtStopDate;
    Button buttonProfile, buttonSettings, buttonHome;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_sleep_tracker);
        setTitle("Sleep Tracker");
        final Calendar myCalendar = Calendar.getInstance();

        ((TimePicker)findViewById(R.id.startTimePicker)).setIs24HourView(true);
        ((TimePicker)findViewById(R.id.stopTimePicker)).setIs24HourView(true);
        Spinner spinner = (Spinner) findViewById(R.id.option_spinner);
        List<String> categories = new ArrayList<String>();
        for (int i = 5; i >= 1; i--) {
            categories.add(Integer.toString(i));
        }
        ArrayAdapter<String> dataAdapter = new ArrayAdapter<String>(this, R.layout.spinner_item, categories);
        dataAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spinner.setAdapter(dataAdapter);

        txtStartDate = (EditText)findViewById(R.id.txtStartdate);
        txtStopDate = (EditText)findViewById(R.id.txtStopdate);
        final EditText[] selecting = {null};
        final DatePickerDialog.OnDateSetListener date = new DatePickerDialog.OnDateSetListener() {
            //Convert datepicker value into string and fill textbox
            @Override
            public void onDateSet(DatePicker view, int year, int monthOfYear,
                                  int dayOfMonth) {
                myCalendar.set(Calendar.YEAR, year);
                myCalendar.set(Calendar.MONTH, monthOfYear);
                myCalendar.set(Calendar.DAY_OF_MONTH, dayOfMonth);
                SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy", Locale.UK);
                selecting[0].setText(sdf.format(myCalendar.getTime()));
            }
        };
        final DatePickerDialog datePicker = new DatePickerDialog(context);
        datePicker.setOnDateSetListener(date);
        txtStartDate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                selecting[0] = txtStartDate;
                datePicker.show();
            }
        });
        txtStopDate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                selecting[0] = txtStopDate;
                datePicker.show();
            }
        });
        datePicker.setOnShowListener(new DialogInterface.OnShowListener() {
            @Override
            public void onShow(DialogInterface arg0) {
                datePicker.getButton(AlertDialog.BUTTON_NEGATIVE).setTextColor(getResources().getColor(R.color.NoColour, null));
                datePicker.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(getResources().getColor(R.color.YesColour, null));
            }
        });
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









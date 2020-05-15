package com.kalma.MainApp;

import android.app.DatePickerDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.drawable.GradientDrawable;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TimePicker;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat;

import com.android.volley.VolleyError;
import com.github.mikephil.charting.charts.LineChart;
import com.github.mikephil.charting.components.XAxis;
import com.github.mikephil.charting.components.YAxis;
import com.github.mikephil.charting.data.Entry;
import com.github.mikephil.charting.data.LineData;
import com.github.mikephil.charting.data.LineDataSet;
import com.github.mikephil.charting.formatter.ValueFormatter;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.Data.AuthStrings;
import com.kalma.Data.LineGraphEntry;
import com.kalma.Data.DataEntry;
import com.kalma.R;

import net.danlew.android.joda.JodaTimeAndroid;

import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.joda.time.Days;
import org.joda.time.Duration;
import org.joda.time.Interval;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.joda.time.format.ISODateTimeFormat;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.math.BigDecimal;
import java.math.RoundingMode;
import java.nio.charset.StandardCharsets;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Objects;
import java.util.TimeZone;

public class SleepTrackerActivity extends AppCompatActivity {
    Context context = this;

    EditText txtStartDate, txtStopDate, txtGraphDate;
    Button buttonProfile, buttonSettings, buttonHome, buttonSend;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_sleep_tracker);
        JodaTimeAndroid.init(this);

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
        txtGraphDate = (EditText)findViewById(R.id.txtGraphStartDate);
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
        final DatePickerDialog.OnDateSetListener date2 = new DatePickerDialog.OnDateSetListener() {
            //Convert datepicker value into string and fill textbox
            @Override
            public void onDateSet(DatePicker view, int year, int monthOfYear,
                                  int dayOfMonth) {
                myCalendar.set(Calendar.YEAR, year);
                myCalendar.set(Calendar.MONTH, monthOfYear);
                myCalendar.set(Calendar.DAY_OF_MONTH, dayOfMonth);
                SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy", Locale.UK);
                selecting[0].setText(sdf.format(myCalendar.getTime()));
                DateTimeZone.setDefault(DateTimeZone.forTimeZone(TimeZone.getDefault()));
                DateTimeFormatter formatter = DateTimeFormat.forPattern("dd/MM/yyy").withZone(DateTimeZone.getDefault());
                DateTime today = new DateTime(formatter.parseDateTime(selecting[0].getText().toString()), DateTimeZone.getDefault());
                DateTime lastWeek = today.minusWeeks(1);
                lastWeek = lastWeek.withHourOfDay(0).withMinuteOfHour(0).withSecondOfMinute(0);
                AuthStrings.getInstance(context).setLastStart(lastWeek);
                AuthStrings.getInstance(context).setLastToday(today);
                getData();
            }
        };

        final DatePickerDialog datePicker = new DatePickerDialog(context);
        final DatePickerDialog datePicker2 = new DatePickerDialog(context);
        datePicker.setOnDateSetListener(date);
        datePicker2.setOnDateSetListener(date2);
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
        txtGraphDate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                selecting[0] = txtGraphDate;
                datePicker2.show();
            }
        });
        datePicker.setOnShowListener(new DialogInterface.OnShowListener() {
            @Override
            public void onShow(DialogInterface arg0) {
                datePicker.getButton(AlertDialog.BUTTON_NEGATIVE).setTextColor(ContextCompat.getColor(context, R.color.NoColour));
                datePicker.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(ContextCompat.getColor(context, R.color.YesColour));
            }
        });
        datePicker2.setOnShowListener(new DialogInterface.OnShowListener() {
            @Override
            public void onShow(DialogInterface arg0) {
                datePicker2.getButton(AlertDialog.BUTTON_NEGATIVE).setTextColor(ContextCompat.getColor(context, R.color.NoColour));
                datePicker2.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(ContextCompat.getColor(context, R.color.YesColour));
            }
        });

        buttonSend = findViewById(R.id.btnAddData);
        buttonSend.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                extractData();
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


        DateTime today = new DateTime().withMinuteOfHour(0).withSecondOfMinute(0);
        DateTime lastWeek = today.minusWeeks(1);
        lastWeek = lastWeek.withHourOfDay(16).withMinuteOfHour(0).withSecondOfMinute(0);
        AuthStrings.getInstance(context).setLastStart(lastWeek);
        AuthStrings.getInstance(context).setLastToday(today);
        getData();
    }


    private void getData( ) {
        //create a json object and call API to log in
        DateTime prevWeek = AuthStrings.getInstance(context).getLastStart();
        DateTime today = AuthStrings.getInstance(context).getLastToday().plusDays(1);
        String lastWeekStr = prevWeek.toString(DateTimeFormat.forPattern("yyyy-MM-dd"));
        String todayStr = today.toString(DateTimeFormat.forPattern("yyyy-MM-dd"));

        String getLink = Objects.requireNonNull(AuthStrings.getInstance(getApplicationContext()).getLinks().get("sleep")).toString()
                + "?from=" + lastWeekStr + "&to=" + todayStr;
        String url = context.getResources().getString(R.string.api_url) + getLink;
        Log.i("REQUEST", url);
        APICaller apiCaller = new APICaller(context.getApplicationContext());
        apiCaller.getData( true,null, buildMap(), getLink, new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                       // Log.e("RESPONSE", response.toString() );
                        try {
                            JSONArray periods = response.getJSONArray(  "periods");
                            DataEntry[] entries  = new DataEntry[periods.length()];
                            DateTimeFormatter parser = ISODateTimeFormat.dateTimeParser();
                            for (int i = 0; i < periods.length(); i++) {
                                int id = periods.getJSONObject(i).getInt("id");
                                DateTime startTime = parser.parseDateTime(periods.getJSONObject(i).getString("start_time"));
                                DateTime stopTime = parser.parseDateTime(periods.getJSONObject(i).getString("stop_time"));
                                int duration = periods.getJSONObject(i).getInt("duration");
                                Log.i("dur", Integer.toString(duration));
                                int rating = periods.getJSONObject(i).getInt("sleep_quality");
                                DataEntry newEntry = new DataEntry(id, startTime, stopTime, duration, rating);
                                newEntry.setDurationText(periods.getJSONObject(i).getString("duration_text"));
                                newEntry.setPercentage(periods.getJSONObject(i).getInt("progress_percentage"));
                                newEntry.setMessage(periods.getJSONObject(i).getString("progress_message"));
                                entries[i] = newEntry;
                            }
                            processData(entries);

                            Log.i("Response", response.toString());
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }

                    @Override
                    public void onFail(VolleyError error) {
                        Log.e("ERROR", error.toString());
                        error.printStackTrace();
                        try {
                            //retrieve error message and display

                            String jsonInput = new String(Objects.requireNonNull(error.networkResponse.data, "utf-8"));
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            Log.e("Error.Response", responseBody.toString());
                            Toast toast = Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG);
                            toast.show();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                        catch (NullPointerException ne){
                            Log.e("NULL POINTER ERROR", "ERROR");
                            error.printStackTrace();
                        }
                    }
                }
        );

    }

    private void processData(DataEntry[] sleepDataEntries) {
        DateTime startDate = AuthStrings.getInstance(context).getLastStart();
        DateTime stopDate = AuthStrings.getInstance(context).getLastToday().withHourOfDay(16);

        List<LineGraphEntry> graphEntries = new ArrayList<LineGraphEntry>();
        int days = Days.daysBetween(startDate.withHourOfDay(0), stopDate.withHourOfDay(0)).getDays();
        for (int i = 0; i <= days; i++) {
            DateTime newDate = startDate.plusDays(i);
            graphEntries.add(new LineGraphEntry(newDate, 0));
        }


        for (DataEntry entry : sleepDataEntries) {
            for (int i = 0; i < graphEntries.size() - 1; i++) {
                Interval interval = new Interval(graphEntries.get(i).getDate(), graphEntries.get(i + 1).getDate());
                if (interval.contains(entry.getStartTime())) {
                    if (interval.contains(entry.getStopTime())) {
                        Interval calcInterval = new Interval(entry.getStartTime(), entry.getStopTime());
                        addHours(graphEntries, i, calcInterval);
                    } else {
                        Interval calcInterval = new Interval(entry.getStartTime(), interval.getEnd());
                        addHours(graphEntries, i, calcInterval);
                    }
                } else if (interval.contains(entry.getStopTime())) {
                    Interval calcInterval = new Interval(interval.getStart(), entry.getStopTime());
                    addHours(graphEntries, i, calcInterval);
                }
            }
        }
        graphData(graphEntries);
    }

    private void graphData(List<LineGraphEntry> graphEntries) {
        final ArrayList<String> xLabel = new ArrayList<>();
        for (LineGraphEntry entry : graphEntries) {
            xLabel.add(entry.getDate().getDayOfMonth() + "/" + String.format(Locale.UK, "%02d", entry.getDate().getMonthOfYear()));
        }
        List<Entry> entries = new ArrayList<Entry>();
        for (int i = 0; i < graphEntries.size(); i++) {
            entries.add(new Entry(i, graphEntries.get(i).getValue()));
        }

        LineDataSet dataSet = new LineDataSet(entries, "Time slept (on the night of)");
        //todo styling here
        //dataSet.setColor(...);
        dataSet.setLineWidth(1);
        dataSet.setColor(ContextCompat.getColor(context, R.color.colorPrimary));
        int[] colors = {ContextCompat.getColor(context, R.color.textOnDark),
                ContextCompat.getColor(context, R.color.colorSecondary)};
        dataSet.setDrawFilled(true);
        GradientDrawable gradientDrawable = new GradientDrawable();
        gradientDrawable.setColors(colors);
        //gradientDrawable.set
        dataSet.setFillDrawable(gradientDrawable);
        LineData lineData = new LineData(dataSet);
        LineChart chart = findViewById(R.id.chart);
        YAxis yAxis = chart.getAxisLeft();
        yAxis.setAxisMinimum(0f);
        XAxis xAxis = chart.getXAxis();
        xAxis.setLabelCount(xLabel.size());
        xAxis.setGranularity(1f);
        xAxis.setValueFormatter(new ValueFormatter() {
            @Override
            public String getFormattedValue(float value) {
                return xLabel.get((int) value);
            }
        });
        chart.getAxisRight().setEnabled(false);
        chart.getXAxis().setPosition(XAxis.XAxisPosition.BOTTOM);
        chart.getDescription().setEnabled(false);
        chart.setData(lineData);
        chart.invalidate();
    }

    private void addHours(List<LineGraphEntry> graphEntries, int i, Interval calcInterval) {
        Duration intervalDuration = calcInterval.toDuration();
        float millis = Float.parseFloat(Double.toString(intervalDuration.getMillis()));
        float hours = (millis / 3.6f) * 1E-6f;
        BigDecimal bd = new BigDecimal(Double.toString(hours));
        bd = bd.setScale(3, RoundingMode.HALF_UP);
        hours = bd.floatValue();
        graphEntries.get(i).setValue(graphEntries.get(i).getValue() + hours);
    }


    private void extractData() {
        Spinner spinner = (Spinner)findViewById(R.id.option_spinner);
        TimePicker startTimePicker = (TimePicker) findViewById(R.id.startTimePicker);
        DateTimeZone.setDefault(DateTimeZone.forTimeZone(TimeZone.getDefault()));
        DateTimeFormatter formatter = DateTimeFormat.forPattern("dd/MM/yyy").withZone(DateTimeZone.getDefault());
        DateTime startDateTime = new DateTime(formatter.parseDateTime(txtStartDate.getText().toString()), DateTimeZone.getDefault())
                .withHourOfDay(startTimePicker.getHour())
                .withMinuteOfHour(startTimePicker.getMinute());

        TimePicker stopTimePicker = (TimePicker) findViewById(R.id.stopTimePicker);
        DateTime stopDateTime = new DateTime(formatter.parseDateTime(txtStopDate.getText().toString()), DateTimeZone.getDefault())
                .withHourOfDay(stopTimePicker.getHour())
                .withMinuteOfHour(stopTimePicker.getMinute());

        if (startDateTime.isAfter(stopDateTime)){
            Toast toast = Toast.makeText(getApplicationContext(), "Stop time must be after start time", Toast.LENGTH_LONG);
            toast.show();
            return;
        }
        if (stopDateTime.isAfter(new DateTime())){
            Toast toast = Toast.makeText(getApplicationContext(), "You cannot make entries for the future!", Toast.LENGTH_LONG);
            toast.show();
            return;
        }

        int rating = Integer.parseInt(spinner.getSelectedItem().toString());
        String startISO8601 = startDateTime.toString(DateTimeFormat.forPattern("yyyy-MM-dd'T'HH:mm:ss").withZoneUTC());
        String stopISO68601 = stopDateTime.toString(DateTimeFormat.forPattern("yyyy-MM-dd'T'HH:mm:ss").withZoneUTC());

        JSONObject object = null;
        try {
            object = createSendObject(rating, startISO8601, stopISO68601);
        }catch (JSONException e){
            e.printStackTrace();
        }
        sendCreate(object);
    }

    private JSONObject createSendObject(int rating, String startISO8601, String stopISO68601) throws JSONException {
        JSONObject entry = new JSONObject();
        entry.put("start_time", startISO8601);
        entry.put("stop_time", stopISO68601);
        entry.put("sleep_quality",  rating);
        JSONArray periods = new JSONArray();
        periods.put(entry);
        JSONObject object = new JSONObject();
        object.put("periods", periods);
        Log.i("BODY", object.toString());
        return object;
    }

    private Map buildMap() {
        Map<String, String> params = new HashMap<String, String>();
        params.put("Authorization", "Bearer " + AuthStrings.getInstance(getApplicationContext()).getAuthToken());
        return params;
    }
    public void sendCreate(JSONObject body) {
        //create a json object and call API to log in


        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.post(true, body, buildMap(), AuthStrings.getInstance(getApplicationContext()).getLinks().get("sleep").toString(), new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        try {
                            //retrieve access token and store.
                            Toast toast = Toast.makeText(getApplicationContext(), response.getString("message"), Toast.LENGTH_LONG);
                            toast.show();
                            Log.i("Response SEND DATA", response.toString());
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                        getData();
                    }
                    @Override
                    public void onFail(VolleyError error) {
                        try {
                            //retrieve error message and display
                            String jsonInput = new String(error.networkResponse.data, StandardCharsets.UTF_8);
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            Log.w("Error.Response", jsonInput);
                            Toast toast = Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG);
                            toast.show();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }
                }
        );
    }
}








